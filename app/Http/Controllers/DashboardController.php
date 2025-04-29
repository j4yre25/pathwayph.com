<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Dashboard', [
            'userNotApproved' => !$user->is_approved,
            'summary' => [
                'total_jobs' => 10,
                'total_applications' => 25,
                'total_hires' => 5
            ],
        ]);


    }
}


