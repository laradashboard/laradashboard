@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Edit Lesson Note</h1>
            
            <!-- Lesson Details -->
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Lesson Details</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ \Carbon\Carbon::parse($lessonResult->course_date)->format('Y-m-d') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Student</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ $lessonResult->userCourse->user->name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Course</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ $lessonResult->userCourse->course->title }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Scheduled Time</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                            {{ \Carbon\Carbon::parse($lessonResult->userCourse->lesson_hour)->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Note Form -->
            <form action="{{ route('teacher.update-note', $lessonResult) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson Status*</label>
                    <select id="status" name="status" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="completed" {{ $lessonResult->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="missed" {{ $lessonResult->status === 'missed' ? 'selected' : '' }}>Missed</option>
                        <option value="rescheduled" {{ $lessonResult->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="teacher_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teacher Notes*</label>
                    <textarea id="teacher_note" name="teacher_note" rows="6" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                        placeholder="Enter your notes about this lesson...">{{ old('teacher_note', $lessonResult->teacher_note) }}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('teacher.lesson-history') }}" 
                       class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Update Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection