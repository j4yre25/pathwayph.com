<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;

class CertificationController extends Controller
{
    // Add certification
    public function addCertification(Request $request)
    {
        $request->validate([
            'graduate_certification_name' => 'required|string|max:255',
            'graduate_certification_issuer' => 'required|string|max:255',
            'graduate_certification_issue_date' => 'required|date',
            'graduate_certification_expiry_date' => 'nullable|date',
            'graduate_certification_credential_id' => 'nullable|string|max:255',
            'graduate_certification_credential_url' => 'nullable|string|max:255',
        ]);

        $certification = new Certification($request->all());
        $certification->user_id = auth()->id(); // Assuming you have a user_id field
        $certification->save();

        return response()->json(['message' => 'Certification added successfully.']);
    }

    // Update certification
    public function updateCertification(Request $request, $id)
    {
        $request->validate([
            'graduate_certification_name' => 'required|string|max:255',
            'graduate_certification_issuer' => 'required|string|max:255',
            'graduate_certification_issue_date' => 'required|date',
            'graduate_certification_expiry_date' => 'nullable|date',
            'graduate_certification_credential_id' => 'nullable|string|max:255',
            'graduate_certification_credential_url' => 'nullable|string|max:255',
        ]);

        $certification = Certification::findOrFail($id);
        $certification->update($request->all());

        return response()->json(['message' => 'Certification updated successfully.']);
    }

    // Remove certification
    public function removeCertification($id)
    {
        $certification = Certification::findOrFail($id);
        $certification->delete();

        return response()->json(['message' => 'Certification removed successfully.']);
    }
}