<?php

// app/Http/Controllers/UserCourseController.php
namespace App\Http\Controllers\Backend;

use App\Models\UserCourse;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCourseController extends Controller
{
    public function index()
    {
        $userCourses = UserCourse::with(['user', 'course'])->get();
        return view('user-courses.index', compact('userCourses'));
    }

    public function create()
    {
        $users = User::all();
        $courses = Course::all();
        return view('user-courses.create', compact('users', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'lesson_day' => 'required|in:even,odd,every',
            'lesson_hour' => 'required|date_format:H:i',
            'lesson_count' => 'required|integer',
        ]);

        UserCourse::create([
            'user_id' => $validated['user_id'],
            'course_id' => $validated['course_id'],
            'buy_date' => now(),
            'lesson_day' => $validated['lesson_day'],
            'lesson_hour' => $validated['lesson_hour'],
            'status' => 'active',
            'lesson_count' => $validated['lesson_count'],
        ]);

        return redirect()->route('user-courses.index')->with('success', 'User course created successfully.');
    }

    public function show(UserCourse $userCourse)
    {
        return view('user-courses.show', compact('userCourse'));
    }

    public function edit(UserCourse $userCourse)
    {
        $users = User::all();
        $courses = Course::all();
        return view('user-courses.edit', compact('userCourse', 'users', 'courses'));
    }

    public function update(Request $request, UserCourse $userCourse)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'lesson_day' => 'required|in:even,odd,every',
            'lesson_hour' => 'required|date_format:H:i',
            'status' => 'required|string',
            'lesson_count' => 'required|integer',
        ]);

        $userCourse->update($validated);

        return redirect()->route('user-courses.index')->with('success', 'User course updated successfully.');
    }

    public function destroy(UserCourse $userCourse)
    {
        $userCourse->delete();
        return redirect()->route('user-courses.index')->with('success', 'User course deleted successfully.');
    }

    // Student methods
    public function myCourses()
    {
        $userCourses = auth()->user()->userCourses()->with('course')->get();
        return view('backend.pages.student.my-courses', compact('userCourses'));
    }

    public function enroll(Course $course)
    {
        return view('backend.pages.student.enroll', compact('course'));
    }

    public function processEnrollment(Request $request, Course $course)
    {
        $validated = $request->validate([
            'lesson_day' => 'required|in:even,odd,every',
            'lesson_hour' => 'required|date_format:H:i',
        ]);

        $userCourse = UserCourse::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'buy_date' => now(),
            'lesson_day' => $validated['lesson_day'],
            'lesson_hour' => $validated['lesson_hour'],
            'status' => 'pending_payment',
            'lesson_count' => $course->lesson_count,
        ]);

        // Redirect to payment page
        return redirect()->route('student.payment', $userCourse);

        // return redirect()->route('payment.create', $userCourse);
    }
    

}