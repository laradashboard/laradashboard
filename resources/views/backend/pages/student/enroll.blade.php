@extends('backend.layouts.app')


@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Enroll in {{ $course->title }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Select your preferred schedule</p>
            
            <form action="{{ route('student.process-enrollment', $course) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Lesson Day -->
                    <div>
                        <label for="lesson_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Lesson Days*
                        </label>
                        <select id="lesson_day" name="lesson_day" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Select days</option>
                            <option value="even">Even Days (2nd, 4th, etc.)</option>
                            <option value="odd">Odd Days (1st, 3rd, etc.)</option>
                            <option value="every">Every Day</option>
                        </select>
                    </div>
                    
                    <!-- Lesson Time -->
                    <div>
                        <label for="lesson_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Lesson Time*
                        </label>
                        <input type="time" id="lesson_hour" name="lesson_hour" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                    </div>
                </div>
                
                <!-- Course Summary -->
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Course Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Price</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">${{ number_format($course->price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Lessons</p>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $course->lesson_count }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Continue to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection