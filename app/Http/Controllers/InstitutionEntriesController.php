<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

use Illuminate\Http\Request;

class InstitutionEntriesController extends Controller
{
    public function index()
    {
        return Inertia::render('Institutions/ManageEntries/Entries');

    }
}
