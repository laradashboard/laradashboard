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
        $userCourses = UserCourse::with(['user', 'course', 'teacher'])
            ->latest()
            ->paginate(10);
            
        return view('backend.pages.enrollments.index', compact('userCourses'));
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
        return view('backend.pages.enrollments.edit', [
            'enrollment' => $userCourse,
            'courses' => Course::all(),
            'users' => User::role('student')->get(),
            'teachers' => User::role('teacher')->get() 
        ]);
    }

    public function update(Request $request, UserCourse $userCourse)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:users,id',
                'lesson_day' => 'required|in:even,odd,every',
                'lesson_hour' => 'required|date_format:H:i',
                'status' => 'required|in:pending_payment,approved,rejected,completed',
                'lesson_count' => 'required|integer|min:1',
                'payment_status' => 'required_if:status,approved|in:pending_verification,verified,failed',
            ]);

            // Explicitly set each field to ensure proper formatting
            $userCourse->update([
                'user_id' => $validated['user_id'],
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
                'lesson_day' => $validated['lesson_day'],
                'lesson_hour' => $validated['lesson_hour'] . ':00', // Add seconds for DB storage
                'status' => $validated['status'],
                'lesson_count' => $validated['lesson_count'],
                'payment_status' => $validated['payment_status'] ?? null, // Ensure null if not provided
            ]);

            return redirect()
                ->route('user-courses.index')
                ->with('success', 'Enrollment updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
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
    public function showReceipt(UserCourse $userCourse)
    {
        if (!$userCourse->payment_receipt_path) {
            abort(404);
        }

        return response()->file(storage_path('app/' . $userCourse->payment_receipt_path));
    }    

}