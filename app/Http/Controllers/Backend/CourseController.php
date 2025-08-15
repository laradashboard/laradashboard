<?php

namespace App\Http\Controllers\Backend;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return view('backend.pages.courses.index', compact('courses'));
    }

    public function create()
    {
        // $this->checkAuthorization(Auth::user(), ['course.create']);
        return view('backend.pages.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'ebook_file' => 'nullable|file|mimes:pdf',
            'lesson_count' => 'required|integer',
        ]);

        $ebookPath = null;
        if ($request->hasFile('ebook_file')) {
            $ebookPath = $request->file('ebook_file')->store('ebooks', 'public');
        }

        Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'ebook_file_path' => $ebookPath,
            'lesson_count' => $validated['lesson_count'],
        ]);

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        return view('backend.pages.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('backend.pages.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'ebook_file' => 'nullable|file|mimes:pdf',
            'lesson_count' => 'required|integer',
        ]);

        $ebookPath = $course->ebook_file_path;
        if ($request->hasFile('ebook_file')) {
            $ebookPath = $request->file('ebook_file')->store('ebooks', 'public');
        }

        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'ebook_file_path' => $ebookPath,
            'lesson_count' => $validated['lesson_count'],
        ]);

        return redirect()->route('backend.pages.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('backend.pages.courses.index')->with('success', 'Course deleted successfully.');
    }
}
