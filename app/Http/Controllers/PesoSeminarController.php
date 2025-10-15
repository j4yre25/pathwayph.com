<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seminar;
use App\Models\SeminarAttendance;
use Inertia\Inertia;

class PesoSeminarController extends Controller
{
    public function index()
    {
        $seminars = Seminar::whereNull('archived_at')->orderByDesc('date')->get();
        dd($seminars);
        return Inertia::render('Admin/CareerGuidance', [
            'seminars' => $seminars,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d\TH:i', // HTML5 datetime-local format
            'description' => 'nullable|string',
        ]);
        Seminar::create($data);
        return back()->with('flash.banner', 'Seminar added.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        'date' => 'required|date_format:Y-m-d\TH:i', // HTML5 datetime-local format
            'description' => 'nullable|string',
        ]);
        $seminar = Seminar::findOrFail($id);
        $seminar->update($data);
        return back();
    }

    public function archive($id)
    {
        $seminar = Seminar::findOrFail($id);
        $seminar->update(['archived_at' => now()]);
        return back();
    }


    public function show($id)
    {
        $seminar = Seminar::with('attendances')->findOrFail($id);
        return Inertia::render('Admin/SeminarDetails', [
            'seminar' => $seminar,
            'attendances' => $seminar->attendances,
        ]);
    }

    public function addAttendance(Request $request, $seminarId)
    {
        $attendees = $request->input('attendees', []);
        foreach ($attendees as $data) {
            $validated = \Validator::make($data, [
                'attendee_name' => 'required|string|max:255',
                'gender' => 'nullable|string|max:10',
                'age' => 'nullable|integer|maxdigits:3',
                'address' => 'nullable|string|max:255',
                'contact_number' => [
                    'required',
                    'regex:/^09\d{9}$/',
                    'digits:11',
                ],
            ])->validate();
            $validated['seminar_id'] = $seminarId;
            SeminarAttendance::create($validated);
        }
        return back();
    }

    public function attendees($id)
    {
        $seminar = Seminar::findOrFail($id);
        $attendees = $seminar->attendances()->paginate(10); // 10 per page
        return response()->json($attendees);
    }
}
