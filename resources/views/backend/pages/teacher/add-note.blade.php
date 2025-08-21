@extends('backend.layouts.app')


@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Lesson Note</h1>
            
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900">Lesson Details</h2>
                <div class="mt-2 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="text-sm font-medium text-gray-900">{{ $lessonResult->course_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Student</p>
                        <p class="text-sm font-medium text-gray-900">{{ $lessonResult->userCourse->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Course</p>
                        <p class="text-sm font-medium text-gray-900">{{ $lessonResult->userCourse->course->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Scheduled Time</p>
                        <p class="text-sm font-medium text-gray-900">{{ $lessonResult->userCourse->lesson_hour }}</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('teacher.save-note', $lessonResult) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="completed" {{ $lessonResult->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="missed" {{ $lessonResult->status === 'missed' ? 'selected' : '' }}>Missed</option>
                        <option value="rescheduled" {{ $lessonResult->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="teacher_note" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="teacher_note" name="teacher_note" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('teacher_note', $lessonResult->teacher_note) }}</textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                        Save Notes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection