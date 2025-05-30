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
        $hr = $user->hr;
        $companyId = $hr->company_id;

        // KPI Metrics for jobs posted by this company
        $totalOpenings = Job::where('company_id', $companyId)
                        ->where('status', 'open')
                        ->count();

        $activeListings = Job::where('company_id', $companyId)
                            ->where('is_approved', 1)
                            ->where('status', 'open')
                            ->count();

        $rolesFilled = Job::where('company_id', $companyId)
                        ->withCount('applications')
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

        // Line Chart Data: Job postings per month
        $monthlyPostings = Job::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('company_id', $companyId)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Area Chart Data: Department job counts per month
        // $areaData = Job::selectRaw("departments.name AS department, DATE_FORMAT(jobs.created_at, '%Y-%m') AS month, COUNT(jobs.id) AS total")
        //     ->join('departments', 'jobs.department_id', '=', 'departments.id')
        //     ->where('jobs.company_id', $companyId)
        //     ->groupBy('departments.name', 'month')
        //     ->orderBy('month')
        //     ->get();

        // Restructure for ECharts Area Chart
        // $grouped = $areaData->groupBy('department');
        // $months = $areaData->pluck('month')->unique()->sort()->values();

        // $areaChartSeries = $grouped->map(function ($entries, $dept) use ($months) {
        //     $monthMap = $entries->keyBy('month');
        //     $data = $months->map(fn($m) => $monthMap[$m]->total ?? 0);
        //     return [
        //         'name' => $dept,
        //         'type' => 'line',
        //         'areaStyle' => [],
        //         'stack' => 'total',
        //         'data' => $data,
        //     ];
        // })->values();

        return Inertia::render('Company/Reports/Reports', [
            'totalOpenings' => $totalOpenings,
            'activeListings' => $activeListings,
            'rolesFilled' => $rolesFilled,
            'typeCounts' => $typeCounts,
            'jobPostingTrends' => $monthlyPostings,
            // 'areaChartLabels' => $months,
            // 'areaChartSeries' => $areaChartSeries,
        ]);
    }
}