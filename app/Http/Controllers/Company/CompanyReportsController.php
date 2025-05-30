<?php


namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyReportsController extends Controller
{
   public function overview(Request $request)
    {
        $user = auth()->user();

        // Get the company_id of the logged-in user via HR account
        $hr = $user->hr; // assuming you have a relationship: User → HumanResource → company_id
        $companyId = $hr->company_id;

        // KPI Metrics for jobs posted by this company
        $totalOpenings = Job::where('company_id', $companyId)->count();
        $activeListings = Job::where('company_id', $companyId)
                            ->where('is_approved', 1)
                            ->where('status', 'open')
                            ->count();
        $rolesFilled = Job::where('company_id', $companyId)
                        ->withcount('applications')
                        ->where('status', 'filled')
                        ->count();

        // Job types for pie chart
        $types = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];
        $typeCounts = [];

        foreach ($types as $type) {
            $typeCounts[$type] = Job::where('company_id', $companyId)
                ->whereHas('jobTypes', function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->count();
        }

        return Inertia::render('Company/Reports/Reports', [
            'totalOpenings' => $totalOpenings,
            'activeListings' => $activeListings,
            'rolesFilled' => $rolesFilled,
            'typeCounts' => $typeCounts,
        ]);
    }

}