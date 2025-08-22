@extends('backend.layouts.app')


@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Complete Payment</h1>
            
            <!-- Enrollment Summary -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="font-medium text-gray-800 dark:text-gray-200 mb-3">Enrollment Summary</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Course</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $course->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Schedule</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ ucfirst($enrollmentData['lesson_day']) }} days at {{ $enrollmentData['lesson_hour'] }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Price</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            ${{ number_format($enrollmentData['course_price'], 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Lessons</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $enrollmentData['lesson_count'] }} lessons
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('student.payment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label for="payment_receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Upload Payment Receipt*
                    </label>
                    <input type="file" id="payment_receipt" name="payment_receipt" required
                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-100
                            hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">JPEG, PNG, or PDF (max 2MB)</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('courses.show', $course) }}" 
                       class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700">
                        Complete Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection