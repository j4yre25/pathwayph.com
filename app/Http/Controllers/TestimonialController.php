<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    // Add testimonial
    public function addTestimonial(Request $request)
    {
        $request->validate([
            'graduate_testimonials_name' => 'required|string|max:255',
            'graduate_testimonials_role_title' => 'required|string|max:255',
            'graduate_testimonials_relationship' => 'required|string|max:255',
            'graduate_testimonials_testimonial' => 'required|string',
        ]);

        $testimonial = new Testimonial($request->all());
        $testimonial->user_id = auth()->id(); // Assuming you have a user_id field
        $testimonial->save();

        return response()->json(['message' => 'Testimonial added successfully.']);
    }

    // Update testimonial
    public function updateTestimonial(Request $request, $id)
    {
        $request->validate([
            'graduate_testimonials_name' => 'required|string|max:255',
            'graduate_testimonials_role_title' => 'required|string|max:255',
            'graduate_testimonials_relationship' => 'required|string|max:255',
            'graduate_testimonials_testimonial' => 'required|string',
        ]);

        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update($request->all());

        return response()->json(['message' => 'Testimonial updated successfully.']);
    }

    // Remove testimonial
    public function removeTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json(['message' => 'Testimonial removed successfully.']);
    }
}