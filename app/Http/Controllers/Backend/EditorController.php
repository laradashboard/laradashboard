<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EditorController extends Controller
{
    /**
     * Handle image uploads from the Editor
     */
    public function upload(Request $request)
    {
        $this->authorize('create', Media::class);

        // Validate the uploaded file
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif|max:2048', // Allow only image files up to 2MB
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();

            // Save file to public storage
            $path = 'uploads/editor/'.date('Y/m');
            $file->move(public_path($path), $fileName);

            // Return the URL to Editor.
            return response()->json([
                'location' => asset($path.'/'.$fileName),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
