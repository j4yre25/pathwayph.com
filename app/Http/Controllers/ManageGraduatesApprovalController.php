<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageGraduatesApprovalController extends Controller
{
    public function index (){


        $graduates = User::where('role', 'graduate')->get();

        return Inertia::render('Institutions/Admin/ManageUsers/Index/Index', [
            'all_users' => $graduates
        ]);

    }

    public function approve(User $user)
    {
        // Check if the user has the role 'graduates'
        if ($user->role === 'graduate') {
            $user->is_approved = true; // Ensure this matches your database field
            $user->save();
    
            return redirect()->route('institution.manage_users')->with('flash.banner', 'Graduate approved successfully.');
        }
    
        // If the user does not have the role 'graduates', return an error message
        return redirect()->route('institution.manage_users')->with('flash.banner', 'Only graduates can be approved.');
    }
    

   
}
