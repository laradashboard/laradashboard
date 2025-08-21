<?php

namespace App\Http\Controllers\Backend;

use App\Models\LessonResult;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LessonResultController extends Controller
{
    public function index()
    {
        $lessonResults = LessonResult::with(['userCourse', 'user'])->get();
        return view('lesson-results.index', compact('lessonResults'));
    }

    public function create()
    {
        $userCourses = UserCourse::all();
        return view('lesson-results.create', compact('userCourses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_course_id' => 'required|exists:user_courses,id',
            'status' => 'required|string',
            'teacher_note' => 'nullable|string',
            'course_date' => 'required|date',
        ]);

        LessonResult::create([
            'user_course_id' => $validated['user_course_id'],
            'user_id' => auth()->id(),
            'status' => $validated['status'],
            'teacher_note' => $validated['teacher_note'],
            'course_date' => $validated['course_date'],
            'update_date' => now(),
        ]);

        return redirect()->route('lesson-results.index')->with('success', 'Lesson result created successfully.');
    }

    public function show(LessonResult $lessonResult)
    {
        return view('lesson-results.show', compact('lessonResult'));
    }

    public function edit(LessonResult $lessonResult)
    {
        $userCourses = UserCourse::all();
        return view('lesson-results.edit', compact('lessonResult', 'userCourses'));
    }

    public function update(Request $request, LessonResult $lessonResult)
    {
        $validated = $request->validate([
            'user_course_id' => 'required|exists:user_courses,id',
            'status' => 'required|string',
            'teacher_note' => 'nullable|string',
            'course_date' => 'required|date',
        ]);

        $lessonResult->update([
            'user_course_id' => $validated['user_course_id'],
            'status' => $validated['status'],
            'teacher_note' => $validated['teacher_note'],
            'course_date' => $validated['course_date'],
            'update_date' => now(),
        ]);

        return redirect()->route('lesson-results.index')->with('success', 'Lesson result updated successfully.');
    }

    public function destroy(LessonResult $lessonResult)
    {
        $lessonResult->delete();
        return redirect()->route('lesson-results.index')->with('success', 'Lesson result deleted successfully.');
    }

    // Teacher methods
    public function upcomingLessons()
    {
        $lessons = LessonResult::where('course_date', '>=', now())
            ->whereHas('userCourse', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['userCourse.course', 'userCourse.user'])
            ->orderBy('course_date')
            ->get();

        return view('backend.pages.teacher.upcoming-lessons', compact('lessons'));
    }

    public function lessonHistory()
    {
        $lessons = LessonResult::where('course_date', '<', now())
            ->whereHas('userCourse', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['userCourse.course', 'userCourse.user'])
            ->orderBy('course_date', 'desc')
            ->get();

        return view('backend.pages.teacher.lesson-history', compact('lessons'));
    }

    public function addNote(LessonResult $lessonResult)
    {
        return view('backend.pages.teacher.add-note', compact('lessonResult'));
    }

    public function saveNote(Request $request, LessonResult $lessonResult)
    {
        $validated = $request->validate([
            'teacher_note' => 'required|string',
            'status' => 'required|string',
        ]);

        $lessonResult->update([
            'teacher_note' => $validated['teacher_note'],
            'status' => $validated['status'],
            'update_date' => now(),
        ]);

        return redirect()->route('teacher.lesson-history')->with('success', 'Note saved successfully.');
    }
}