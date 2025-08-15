@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $course->title }}</h2>
                    <p class="text-gray-600 mt-2">{{ $course->description }}</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-gray-800">${{ number_format($course->price, 2) }}</span>
                    <p class="text-gray-600">{{ $course->lesson_count }} lessons</p>
                </div>
            </div>

            @if($course->ebook_file_path)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">E-Book</h3>
                <a href="{{ asset('storage/' . $course->ebook_file_path) }}" 
                   class="text-blue-600 hover:text-blue-800 underline" 
                   target="_blank">
                    Download E-Book
                </a>
            </div>
            @endif

            <div class="mt-8 flex justify-end">
                @role('student')
                <a href="{{ route('student.enroll', $course) }}" 
                   class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Enroll Now
                </a>
                @endrole

                @can('edit courses')
                <a href="{{ route('courses.edit', $course) }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded ml-2">
                    Edit Course
                </a>
                @endcan

                <a href="{{ route('courses.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded ml-2">
                    Back to Courses
                </a>
            </div>
        </div>
    </div>
</div>
@endsection