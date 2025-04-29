<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
Use App\Models\JobApplication;
use Illuminate\Http\Request;

class HiringController extends Controller
{
    public function markAsHired(JobApplication $application)
    {
        $application->status = 'hired';
        $application->save();

        return redirect()->back()->with('flash.banner', 'Applicant hired successfully.');
    }

    public function getHiredCount()
    {
        // Count total hires per job
        $hires = JobApplication::where('status', 'hired')->count();

        return response()->json(['hires' => $hires]);
    }
}
