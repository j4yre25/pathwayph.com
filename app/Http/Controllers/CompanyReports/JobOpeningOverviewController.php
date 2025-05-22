<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobOpeningOverviewController extends Controller
{
    public function index(Request $request)
    {
        $jobs = Job::query()
            ->with('department')
            ->when($request->type, fn ($q) => $q->where('employment_type', $request->type))
            ->get();

        $chartData = [
            'total' => $jobs->count(),
            'active' => $jobs->where('status', 'active')->count(),
            'by_type' => $jobs->groupBy('employment_type')->map->count(),
            'by_department' => $jobs->groupBy('department.name')->map->count(),
        ];

        return Inertia::render('Reports/JobOpening', [
            'filters' => $request->only('type'),
            'chartData' => $chartData,
        ]);
    }
}
