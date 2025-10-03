<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Support\Facades\Log;
use App\Models\Program;
use App\Models\Job;
use App\Models\SeminarRequest;
use App\Models\SeminarAttendance;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstitutionCareerGuidanceController extends Controller
{
    public function index()
    {
        // Existing logic
        $inDemandJobs = Job::where('is_approved', 1)
            ->with('programs', 'category', 'sector')
            ->orderByDesc('created_at')
            ->take(9)
            ->get()
            ->map(function ($job) {
                return [
                    'role' => $job->job_title,
                    'category' => $job->category->name ?? null,
                    'sector' => $job->sector->name ?? null,
                    'skills' => is_array($job->skills) ? $job->skills : json_decode($job->skills, true),
                    'programs' => $job->programs->pluck('name')->toArray(),
                ];
            });

        $allSkills = Job::where('is_approved', 1)->pluck('skills')->toArray();
        $skillsFlat = collect($allSkills)
            ->flatMap(function ($skills) {
                return is_array($skills) ? $skills : json_decode($skills, true);
            })
            ->filter()
            ->map(fn($s) => strtolower(trim($s)))
            ->countBy()
            ->sortDesc()
            ->take(10);

        $jobCounts = Job::where('is_approved', 1)
            ->selectRaw('job_title, count(*) as openings')
            ->groupBy('job_title')
            ->orderByDesc('openings')
            ->take(10)
            ->get();

        // Seminar requests logic
        $institution = Institution::where('user_id', Auth::id())->first();
        $seminarRequests = $institution
            ? SeminarRequest::where('institution_id', $institution->id)->orderByDesc('created_at')->get()
            : collect();

        return Inertia::render('Institutions/CareerCounseling/Index', [
            'inDemandJobs' => $inDemandJobs,
            'topSkills' => $skillsFlat,
            'jobCounts' => $jobCounts,
            'seminarRequests' => $seminarRequests,
        ]);
    }


    public function storeSeminarRequest(Request $request)
    {
        \Log::info('Request received', $request->all());
        \Log::info('Has file?', [$request->hasFile('attachment')]);
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $data = $request->validate([
            'request_for' => 'required|string',
            'event_name' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpeg,png|max:2048',
            'attendees' => 'nullable|string', // Will be JSON
        ]);
        $data['institution_id'] = $institution->id;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('seminar_attachments', 'public');
        }
        \Log::info('Attachment present:', [
            'hasFile' => $request->hasFile('attachment'),
            'file' => $request->file('attachment')
        ]);

        $seminarRequest = SeminarRequest::create($data);

        // Save attendees if provided
        if ($request->filled('attendees')) {
            $attendees = json_decode($request->attendees, true);
            foreach ($attendees as $attendee) {
                if (
                    !empty($attendee['attendee_name']) &&
                    !empty($attendee['gender']) &&
                    isset($attendee['age'])
                ) {
                    SeminarAttendance::create([
                        'seminar_request_id' => $seminarRequest->id,
                        'attendee_name' => $attendee['attendee_name'],
                        'gender' => $attendee['gender'],
                        'age' => $attendee['age'],
                    ]);
                }
            }
        }

        return redirect()->route('institutions.career-guidance')->with('flash.banner', 'Seminar request sent!');
    }

    public function cancelSeminarRequest($id)
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $request = SeminarRequest::where('institution_id', $institution->id)->findOrFail($id);
        $request->update(['status' => 'cancelled']);
        return back()->with('flash.banner', 'Request cancelled.');
    }

    public function showSeminarRequest($id)
    {
        $institution = Institution::where('user_id', Auth::id())->firstOrFail();
        $request = SeminarRequest::where('institution_id', $institution->id)->findOrFail($id);
        return response()->json($request);
    }

    public function attendance($seminarRequestId)
    {
        $seminar = SeminarRequest::findOrFail($seminarRequestId);
        $attendances = SeminarAttendance::where('seminar_request_id', $seminarRequestId)
            ->where(function ($q) {
                $q->whereNotNull('attendee_name')
                    ->orWhereNotNull('gender')
                    ->orWhereNotNull('age');
            })
            ->get();

        // Group by gender and age range for summary
        $byGender = $attendances->groupBy('gender')->map->count();
        $byAge = $attendances->groupBy(function ($item) {
            if ($item->age < 18)
                return 'Under 18';
            if ($item->age < 25)
                return '18-24';
            if ($item->age < 35)
                return '25-34';
            if ($item->age < 50)
                return '35-49';
            return '50+';
        })->map->count();

        return response()->json([
            'attendances' => $attendances,
            'byGender' => $byGender,
            'byAge' => $byAge,
        ]);
    }

    public function storeAttendance(Request $request, $seminarRequestId)
    {
        $data = $request->validate([
            'attendees' => 'required|array',
            'attendees.*.attendee_name' => 'required|string|max:255',
            'attendees.*.gender' => 'required|string|max:20',
            'attendees.*.age' => 'required|integer|min:0|max:120',
        ]);
        foreach ($data['attendees'] as $attendee) {
            SeminarAttendance::create([
                'seminar_request_id' => $seminarRequestId,
                'attendee_name' => $attendee['attendee_name'],
                'gender' => $attendee['gender'],
                'age' => $attendee['age'],
            ]);
        }
        return redirect()->back();
    }

}