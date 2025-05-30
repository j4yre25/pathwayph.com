<?php

namespace App\Http\Controllers;

use App\Models\JobInvitation;
use App\Models\Graduate;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageJobReferralsController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all referrals with related data
        $referrals = JobInvitation::with(['graduate.user', 'job.company'])
            ->latest()
            ->get();

        // Calculate success rate
        $totalReferrals = $referrals->count();
        $successfulReferrals = $referrals->where('status', 'hired')->count();
        $successRate = $totalReferrals > 0 ? round(($successfulReferrals / $totalReferrals) * 100, 2) : 0;


            
        // Prepare data for Vue
        $referralData = $referrals->map(function ($ref) {
            return [
                'id' => $ref->id,
                'candidate' => $ref->graduate && $ref->graduate->user ? $ref->graduate->user->name : 'N/A',
                'job_title' => $ref->job ? $ref->job->job_title : 'N/A',
                'company' => $ref->job && $ref->job->company ? $ref->job->company->company_name : 'N/A',
                'status' => $ref->status,
                'referred_at' => $ref->created_at ? $ref->created_at->toDateString() : '',
                'hired_at' => $ref->status === 'hired' && $ref->updated_at ? $ref->updated_at->toDateString() : null,
            ];
        });

        return Inertia::render('Admin/ManageJobReferrals', [
            'referrals' => $referralData,
            'totalReferrals' => $totalReferrals,
            'successfulReferrals' => $successfulReferrals,
            'successRate' => $successRate,
        ]);
    }
}
