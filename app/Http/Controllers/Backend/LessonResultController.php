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
    $userCourses = UserCourse::where('teacher_id', auth()->id())
        ->where('status', 'approved')
        ->with(['course', 'user', 'lessonResults'])
        ->get();

    $upcomingLessons = collect();

    foreach ($userCourses as $userCourse) {
        $completedLessons = $userCourse->lessonResults->count();
        $remainingLessons = $userCourse->lesson_count - $completedLessons;
        
        if ($remainingLessons <= 0) continue;

        $lessonDates = $this->generateUpcomingLessonDates(
            $userCourse->lesson_day,
            $userCourse->lesson_hour,
            $remainingLessons
        );

        foreach ($lessonDates as $date) {
            $upcomingLessons->push([
                'user_course' => $userCourse,
                'course_date' => $date,
                'lesson_hour' => $userCourse->lesson_hour,
                'student' => $userCourse->user,
                'course' => $userCourse->course,
                'remaining_lessons' => $remainingLessons
            ]);
        }
    }

    $upcomingLessons = $upcomingLessons->sortBy('course_date');
    
    return view('backend.pages.teacher.upcoming-lessons', compact('upcomingLessons'));
}

    private function generateUpcomingLessonDates($lessonDay, $lessonHour, $count = 10)
    {
        $dates = collect();
        $today = now();
        $currentDate = $today->copy();
        
        for ($i = 0; $i < 30 && $dates->count() < $count; $i++) { // Look 30 days ahead max
            if ($this->matchesLessonDay($currentDate, $lessonDay)) {
                $dates->push($currentDate->copy()->setTimeFromTimeString($lessonHour));
            }
            $currentDate->addDay();
        }
        
        return $dates;
    }

    private function matchesLessonDay($date, $lessonDay)
    {
        $dayOfMonth = $date->day;
        
        switch ($lessonDay) {
            case 'even':
                return $dayOfMonth % 2 === 0;
            case 'odd':
                return $dayOfMonth % 2 === 1;
            case 'every':
                return true;
            default:
                return false;
        }
    }

    public function lessonHistory()
    {
        // Get all courses taught by this teacher with their lesson results
        $userCourses = UserCourse::where('teacher_id', auth()->id())
            ->where('status', 'approved')
            ->with(['course', 'user', 'lessonResults' => function($query) {
                $query->orderBy('course_date', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.pages.teacher.lesson-history', compact('userCourses'));
    }

    public function addNote(UserCourse $userCourse, $lessonDate)
    {
        // Verify the teacher owns this course
        // if ($userCourse->teacher_id !== auth()->id()) {
        //     abort(403, 'Unauthorized action.');
        // }

        // Parse the lesson date
        $lessonDate = \Carbon\Carbon::parse($lessonDate);

        return view('backend.pages.teacher.add-note', compact('userCourse', 'lessonDate'));
    }
    public function editNote(LessonResult $lessonResult)
    {
        // Verify the teacher owns this lesson
        if ($lessonResult->userCourse->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('backend.pages.teacher.edit-note', compact('lessonResult'));
    }

    public function updateNote(Request $request, LessonResult $lessonResult)
    {
        // Verify the teacher owns this lesson
        if ($lessonResult->userCourse->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:completed,missed,rescheduled',
            'teacher_note' => 'required|string',
        ]);

        $lessonResult->update([
            'status' => $validated['status'],
            'teacher_note' => $validated['teacher_note'],
            'update_date' => now(),
        ]);

        return redirect()->route('teacher.lesson-history')
            ->with('success', 'Lesson note updated successfully.');
    }

    public function deleteNote(LessonResult $lessonResult)
    {
        // Verify the teacher owns this lesson
        if ($lessonResult->userCourse->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $lessonResult->delete();

        return redirect()->route('teacher.lesson-history')
            ->with('success', 'Lesson note deleted successfully.');
    }
    public function saveNote(Request $request, UserCourse $userCourse)
    {
        // Verify the teacher owns this course
        if ($userCourse->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'course_date' => 'required|date',
            'status' => 'required|in:completed,missed,rescheduled',
            'teacher_note' => 'required|string',
        ]);

        // Create or update the lesson result
        $lessonResult = LessonResult::updateOrCreate(
            [
                'user_course_id' => $userCourse->id,
                'course_date' => $validated['course_date'],
            ],
            [
                'user_id' => $userCourse->user_id,
                'status' => $validated['status'],
                'teacher_note' => $validated['teacher_note'],
                'update_date' => now(),
            ]
        );

        return redirect()->route('teacher.lesson-history')
            ->with('success', 'Lesson note saved successfully.');
    }

    // In your AdminController or appropriate controller
    public function todayLessons()
    {
        $today = now()->format('Y-m-d');
        
        // Get all lessons happening today
        $todayLessons = UserCourse::where('status', 'approved')
            ->whereHas('lessonResults', function($query) use ($today) {
                $query->whereDate('course_date', $today);
            })
            ->with(['course', 'user', 'teacher', 'lessonResults' => function($query) use ($today) {
                $query->whereDate('course_date', $today);
            }])
            ->orderBy('lesson_hour')
            ->get();

        // Also include lessons that are scheduled for today based on their pattern
        // but don't have lessonResults created yet
        $scheduledLessons = UserCourse::where('status', 'approved')
            ->whereDoesntHave('lessonResults', function($query) use ($today) {
                $query->whereDate('course_date', $today);
            })
            ->with(['course', 'user', 'teacher'])
            ->get()
            ->filter(function($userCourse) use ($today) {
                // Check if today matches the lesson day pattern
                return $this->matchesLessonDay(now(), $userCourse->lesson_day);
            })
            ->map(function($userCourse) use ($today) {
                $userCourse->is_scheduled = true;
                $userCourse->lesson_result = null;
                return $userCourse;
            });

        // Combine both results
        $allTodayLessons = $todayLessons->merge($scheduledLessons)->sortBy('lesson_hour');

        return view('backend.pages.teacher.today-lessons', compact('allTodayLessons', 'today'));
    }
}