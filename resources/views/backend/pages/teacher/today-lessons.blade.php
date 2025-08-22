@extends('backend.layouts.app')

@section('admin-content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Today's Lessons - {{ $today }}</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Total: {{ $allTodayLessons->count() }} lessons
            </span>
        </div>
    </div>

    @if($allTodayLessons->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($allTodayLessons as $lesson)
                    <tr>
                        <!-- Time -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($lesson->lesson_hour)->format('H:i') }}
                            </div>
                        </td>
                        
                        <!-- Student -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ $lesson->user->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $lesson->user->email }}
                            </div>
                        </td>
                        
                        <!-- Teacher -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ $lesson->teacher->name ?? 'Not assigned' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $lesson->teacher->email ?? '' }}
                            </div>
                        </td>
                        
                        <!-- Course -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ $lesson->course->title }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $lesson->lesson_count }} lessons total
                            </div>
                        </td>
                        
                        <!-- Schedule -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ ucfirst($lesson->lesson_day) }} days
                        </td>
                        
                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($lesson->is_scheduled) && $lesson->is_scheduled)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Scheduled
                                </span>
                            @elseif($lesson->lessonResults->isNotEmpty())
                                @php $lessonResult = $lesson->lessonResults->first(); @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $lessonResult->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($lessonResult->status === 'missed' ? 'bg-red-100 text-red-800' : 
                                       ($lessonResult->status === 'rescheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($lessonResult->status) }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No record
                                </span>
                            @endif
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if(isset($lesson->is_scheduled) && $lesson->is_scheduled)
                                <span class="text-gray-400">No action needed</span>
                            @elseif($lesson->lessonResults->isNotEmpty())
                                @php $lessonResult = $lesson->lessonResults->first(); @endphp
                                <a href="{{ route('user-courses.edit', $lesson) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Details
                                </a>
                            @else
                                <a href="{{ route('user-courses.edit', $lesson) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Manage
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">No lessons scheduled for today.</p>
        <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
            There are no lessons scheduled for {{ $today }}.
        </p>
    </div>
    @endif
</div>
@endsection