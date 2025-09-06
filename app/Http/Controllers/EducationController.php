<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Education;
use Carbon\Carbon;

class EducationController extends Controller
{
    protected function computeSchoolYear(?string $start, ?string $end, bool $isCurrent): ?string
    {
        if (!$start) return null;
        try {
            $startYear = Carbon::parse($start)->year;
        } catch (\Throwable $e) {
            return null;
        }

        if ($isCurrent || !$end) {
            return $startYear . ' - Present';
        }

        try {
            $endYear = Carbon::parse($end)->year;
        } catch (\Throwable $e) {
            return $startYear . ' - Present';
        }

        return $startYear === $endYear
            ? (string)$startYear
            : $startYear . ' - ' . $endYear;
    }

    // Add education
    public function addEducation(Request $request)
    {
        $data = $request->validate([
            'education' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'achievements' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        $education = new Education();
        $education->education = $data['education'];
        $education->program = $data['program'];
        $education->field_of_study = $data['field_of_study'];
        $education->start_date = $data['start_date'];
        $education->end_date = $data['end_date'] ?? null;
        $education->description = $data['description'] ?? null;
        $education->achievements = $data['achievements'] ?? null;
        $education->is_current = $data['is_current'] ?? false;
        $education->user_id = auth()->id();
        $education->school_year = $this->computeSchoolYear(
            $education->start_date,
            $education->end_date,
            (bool)$education->is_current
        );
        $education->save();

        return response()->json([
            'message' => 'Education added successfully.',
            'education' => $education
        ]);
    }

    // Update education
    public function updateEducation(Request $request, $id)
    {
        $data = $request->validate([
            'education' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'achievements' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        $education = Education::findOrFail($id);

        $education->education = $data['education'];
        $education->program = $data['program'];
        $education->field_of_study = $data['field_of_study'];
        $education->start_date = $data['start_date'];
        $education->end_date = $data['end_date'] ?? null;
        $education->description = $data['description'] ?? null;
        $education->achievements = $data['achievements'] ?? null;
        $education->is_current = $data['is_current'] ?? false;
        $education->school_year = $this->computeSchoolYear(
            $education->start_date,
            $education->end_date,
            (bool)$education->is_current
        );
        $education->save();

        return response()->json([
            'message' => 'Education updated successfully.',
            'education' => $education
        ]);
    }

    // Remove education
    public function removeEducation($id)
    {
        $education = Education::findOrFail($id);
        $education->delete();

        return response()->json(['message' => 'Education removed successfully.']);
    }
}