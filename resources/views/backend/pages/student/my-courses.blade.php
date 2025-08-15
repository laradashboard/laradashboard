@extends('backend.layouts.app')


@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">My Courses</h1>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($userCourses as $userCourse)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800">{{ $userCourse->course->title }}</h2>
                <p class="text-gray-600 mt-2">{{ Str::limit($userCourse->course->description, 100) }}</p>
                
                <div class="mt-4">
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">Schedule:</span> 
                        {{ ucfirst($userCourse->lesson_day) }} days at {{ $userCourse->lesson_hour }}
                    </p>
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">Status:</span> 
                        <span class="capitalize">{{ $userCourse->status }}</span>
                    </p>
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">Lessons Completed:</span> 
                        {{ $userCourse->lessonResults->count() }} of {{ $userCourse->lesson_count }}
                    </p>
                </div>

                <div class="mt-6">
                    <a href="#" class="text-blue-600 hover:text-blue-800 underline">View Progress</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($userCourses->isEmpty())
    <div class="bg-white rounded-lg shadow p-6 text-center">
        <p class="text-gray-600">You haven't enrolled in any courses yet.</p>
        <a href="{{ route('courses.index') }}" class="text-blue-600 hover:text-blue-800 underline mt-2 inline-block">
            Browse Available Courses
        </a>
    </div>
    @endif
</div>
@endsection