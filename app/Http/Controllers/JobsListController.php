<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobsListController extends Controller
{
    public function list(Job $jobs) {

  
        $jobs = Job::all();
        return Inertia::render('Jobs/Index/List', [
            'jobs' => $jobs
        ]);

        
    }

}
