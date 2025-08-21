@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Edit Enrollment</h1>
            
            <form action="{{ route('user-courses.update', $enrollment) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Selection -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student*</label>
                        <select id="user_id" name="user_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $enrollment->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Course Selection -->
                    <div>
                        <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course*</label>
                        <select id="course_id" name="course_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ $enrollment->course_id == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }} (${{ number_format($course->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Schedule -->
                    <div>
                        <label for="lesson_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson Days*</label>
                        <select id="lesson_day" name="lesson_day" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            <option value="even" {{ $enrollment->lesson_day == 'even' ? 'selected' : '' }}>Even Days</option>
                            <option value="odd" {{ $enrollment->lesson_day == 'odd' ? 'selected' : '' }}>Odd Days</option>
                            <option value="every" {{ $enrollment->lesson_day == 'every' ? 'selected' : '' }}>Every Day</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="lesson_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson Time*</label>
                        <input type="time" id="lesson_hour" name="lesson_hour" 
                            value="{{ date('H:i', strtotime($enrollment->lesson_hour)) }}" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                            step="60">
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status*</label>
                        <select id="status" name="status" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            <option value="pending_payment" {{ $enrollment->status == 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                            <option value="approved" {{ $enrollment->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $enrollment->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="completed" {{ $enrollment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    
                    <!-- Payment Status -->
                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Status</label>
                        <select id="payment_status" name="payment_status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            <option value="pending_verification" {{ $enrollment->payment_status == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="verified" {{ $enrollment->payment_status == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="failed" {{ $enrollment->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div>
                        <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teacher*</label>
                        <select id="teacher_id" name="teacher_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $enrollment->teacher_id == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Lesson Count -->
                    <div class="col-span-2">
                        <label for="lesson_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson Count*</label>
                        <input type="number" id="lesson_count" name="lesson_count" value="{{ $enrollment->lesson_count }}" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                    </div>
                </div>
                
                <!-- Receipt Preview -->
                @if($enrollment->payment_receipt_path)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Receipt</label>
                    <a href="{{ route('user-courses.show-receipt', $enrollment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View Current Receipt
                    </a>
                </div>
                @endif
                
                <div class="mt-8 flex justify-end">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Update Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection