@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{__('Lesson History')}}</h1>
    </div>

    @if($userCourses->count() > 0)
    <div class="space-y-4">
        @foreach($userCourses as $userCourse)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <!-- Course Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer" 
                 onclick="toggleCourse('course-{{ $userCourse->id }}')">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            {{ $userCourse->course->title }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Student: {{ $userCourse->user->name }} • 
                            Total Lessons: {{ $userCourse->lesson_count }} • 
                            Completed: {{ $userCourse->lessonResults->count() }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $userCourse->lessonResults->count() }} lessons
                        </span>
                        <svg id="icon-course-{{ $userCourse->id }}" class="w-5 h-5 text-gray-400 transform rotate-0 transition-transform" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Lessons List (Collapsed by default) -->
            <div id="course-{{ $userCourse->id }}" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($userCourse->lessonResults as $lessonResult)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($lessonResult->course_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($userCourse->lesson_hour)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $lessonResult->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($lessonResult->status === 'missed' ? 'bg-red-100 text-red-800' : 
                                           ($lessonResult->status === 'rescheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($lessonResult->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ Str::limit($lessonResult->teacher_note, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('teacher.edit-note', $lessonResult) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                        Edit
                                    </a>
                                    <form action="{{ route('teacher.delete-note', $lessonResult) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                onclick="return confirm('Are you sure you want to delete this lesson note?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No lesson records found for this course.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">No teaching history found.</p>
        <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
            You don't have any completed courses or lessons yet.
        </p>
    </div>
    @endif
</div>

<script>
function toggleCourse(courseId) {
    const courseElement = document.getElementById(courseId);
    const iconElement = document.getElementById('icon-' + courseId);
    
    courseElement.classList.toggle('hidden');
    iconElement.classList.toggle('rotate-0');
    iconElement.classList.toggle('rotate-180');
}

// Optional: Open the first course by default
document.addEventListener('DOMContentLoaded', function() {
    const firstCourse = document.querySelector('[id^="course-"]');
    if (firstCourse) {
        firstCourse.classList.remove('hidden');
        const firstIcon = document.querySelector('[id^="icon-course-"]');
        if (firstIcon) {
            firstIcon.classList.remove('rotate-0');
            firstIcon.classList.add('rotate-180');
        }
    }
});
</script>

<style>
.rotate-0 { transform: rotate(0deg); }
.rotate-180 { transform: rotate(180deg); }
</style>
@endsection