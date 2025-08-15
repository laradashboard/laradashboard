@extends('backend.layouts.app')


@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Complete Payment</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Course: {{ $userCourse->course->title }}</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Payment Options -->
                <div class="border-r border-gray-200 dark:border-gray-700 pr-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Payment Method</h3>
                    
                    <div class="space-y-4">
                        <!-- Bank Transfer (Disabled) -->
                        <div class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg opacity-50 cursor-not-allowed">
                            <div class="flex items-center">
                                <input id="bank-transfer" name="payment_method" type="radio" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" disabled>
                                <label for="bank-transfer" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bank Transfer (Coming Soon)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Receipt Upload -->
                        <div class="p-4 border-2 border-indigo-300 dark:border-indigo-600 rounded-lg bg-indigo-50 dark:bg-indigo-900/20">
                            <div class="flex items-center">
                                <input id="receipt-upload" name="payment_method" type="radio" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                                <label for="receipt-upload" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Upload Payment Receipt
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Summary -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Enrollment Summary</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Schedule</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">
                                {{ ucfirst($userCourse->lesson_day) }} days at {{ $userCourse->lesson_hour }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Amount</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ number_format($userCourse->course->price, 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Receipt Upload Form -->
                    <form action="{{ route('student.payment.store', $userCourse) }}" method="POST" enctype="multipart/form-data" class="mt-8">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="payment_receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Upload Payment Receipt* (JPEG, PNG, PDF)
                            </label>
                            <input type="file" id="payment_receipt" name="payment_receipt" required
                                class="block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-100
                                    hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800">
                        </div>
                        
                        <button type="submit" 
                            class="w-full px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Complete Enrollment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection