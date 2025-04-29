public function uploadResume(Request $request)
{
    $request->validate([
        'resume' => 'required|mimes:pdf,doc,docx|max:2048'
    ]);

    try {
        $user = auth()->user();
        $file = $request->file('resume');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('resumes', $fileName, 'public');

        // Delete old resume if exists
        if ($user->resume) {
            Storage::disk('public')->delete($user->resume->file);
            $user->resume->delete();
        }

        // Create new resume record
        $user->resume()->create([
            'file' => $path,
            'fileName' => $file->getClientOriginalName()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Resume uploaded successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Resume upload error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload resume'
        ], 500);
    }
}

public function deleteResume()
{
    try {
        $resume = auth()->user()->resume;
        if ($resume) {
            Storage::disk('public')->delete($resume->file);
            $resume->delete();
        }
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false], 500);
    }
}