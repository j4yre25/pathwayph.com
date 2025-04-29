<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;

class PortfolioController extends Controller
{
    // Fetch the portfolio data for a specific user
    public function show($id)
    {
        $portfolio = Portfolio::where('user_id', $id)->first();

        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio not found'], 404);
        }

        return response()->json($portfolio);
    }

    // Update the portfolio data
    public function update(Request $request, $id)
    {
        $request->validate([
            'graduate_full_name' => 'required|string|max:255',
            'graduate_professional_title' => 'required|string|max:255',
            'graduate_email' => 'required|email|max:255',
            'graduate_phone' => 'required|string|max:15',
            'graduate_location' => 'required|string|max:255',
            'graduate_about_me' => 'nullable|string',
            'graduate_picture_url' => 'nullable|url',
            'graduate_skills' => 'array',
            'graduate_career_goals' => 'array',
            'graduate_employment_preferences' => 'array',
        ]);

        $portfolio = Portfolio::where('user_id', $id)->first();

        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio not found'], 404);
        }

        $portfolio->update($request->all());

        return response()->json(['message' => 'Portfolio updated successfully', 'portfolio' => $portfolio]);
    }
}