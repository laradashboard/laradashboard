<?php

namespace App\Services;

use App\Services\MenuService\AdminMenuItem;
use Illuminate\Support\Facades\Route;

class MenuService
{
    public function bootstrap()
    {
        // Admin menus
        ld_add_filter('admin_menu_groups_before_sorting', [$this, 'addAdminMenus']);
        
        // Teacher menus
        ld_add_filter('teacher_menu_groups_before_sorting', [$this, 'addTeacherMenus']);
        
        // Student menus
        ld_add_filter('student_menu_groups_before_sorting', [$this, 'addStudentMenus']);
    }

    // Admin Menus
    public function addAdminMenus(array $groups): array
    {
        // Courses Management
        $coursesChildren = [
            (new AdminMenuItem())->setAttributes([
                'label' => __('All Courses'),
                'route' => route('courses.index'),
                'active' => Route::is('courses.index'),
                'priority' => 1,
                'id' => 'courses_index',
                'permissions' => ['view courses'],
            ]),
            (new AdminMenuItem())->setAttributes([
                'label' => __('Add New Course'),
                'route' => route('courses.create'),
                'active' => Route::is('courses.create'),
                'priority' => 2,
                'id' => 'courses_create',
                'permissions' => ['courses.create'],
            ])
        ];

        $groups[__('Management')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Courses'),
            'iconClass' => 'bi bi-book',
            'route' => route('courses.index'),
            'active' => Route::is('courses.*'),
            'id' => 'courses',
            'priority' => 10,
            'permissions' => ['courses.view'],
            'children' => $coursesChildren,
        ]);

        // User Courses Management
        $userCoursesChildren = [
            (new AdminMenuItem())->setAttributes([
                'label' => __('All Enrollments'),
                'route' => route('user-courses.index'),
                'active' => Route::is('user-courses.index'),
                'priority' => 1,
                'id' => 'user_courses_index',
                'permissions' => ['user_courses.view'],
            ]),
            (new AdminMenuItem())->setAttributes([
                'label' => __('Create Enrollment'),
                'route' => route('user-courses.create'),
                'active' => Route::is('user-courses.create'),
                'priority' => 2,
                'id' => 'user_courses_create',
                'permissions' => ['user_courses.create'],
            ])
        ];

        $groups[__('Management')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Enrollments'),
            'iconClass' => 'bi bi-people',
            'route' => route('user-courses.index'),
            'active' => Route::is('user-courses.*'),
            'id' => 'user_courses',
            'priority' => 20,
            'permissions' => ['user_courses.view'],
            'children' => $userCoursesChildren,
        ]);

        // Today's Lessons
        $groups[__('Management')][] = (new AdminMenuItem())->setAttributes([
            'label' => __("Today's Lessons"),
            'iconClass' => 'bi bi-calendar-day',
            'route' => route('admin.dashboard'),
            'active' => Route::is('admin.dashboard'),
            'id' => 'today_lessons',
            'priority' => 30,
            'permissions' => ['lesson_results.view'],
        ]);

        // Payments
        $paymentsChildren = [
            (new AdminMenuItem())->setAttributes([
                'label' => __('All Payments'),
                'route' => route('payments.index'),
                'active' => Route::is('payments.index'),
                'priority' => 1,
                'id' => 'payments_index',
                'permissions' => ['payments.view'],
            ]),
            (new AdminMenuItem())->setAttributes([
                'label' => __('Create Payment'),
                'route' => route('payments.create'),
                'active' => Route::is('payments.create'),
                'priority' => 2,
                'id' => 'payments_create',
                'permissions' => ['payments.create'],
            ])
        ];

        $groups[__('Management')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Payments'),
            'iconClass' => 'bi bi-credit-card',
            'route' => route('payments.index'),
            'active' => Route::is('payments.*'),
            'id' => 'payments',
            'priority' => 40,
            'permissions' => ['payments.view'],
            'children' => $paymentsChildren,
        ]);
        $groups[__('Teaching')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Upcoming Lessons'),
            'iconClass' => 'bi bi-calendar-check',
            'route' => route('teacher.upcoming-lessons'),
            'active' => Route::is('teacher.upcoming-lessons'),
            'id' => 'upcoming_lessons',
            'priority' => 10,
            'permissions' => ['lesson_results.view'],
        ]);

        // Lesson History
        $groups[__('Teaching')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Lesson History'),
            'iconClass' => 'bi bi-clock-history',
            'route' => route('teacher.lesson-history'),
            'active' => Route::is('teacher.lesson-history'),
            'id' => 'lesson_history',
            'priority' => 20,
            'permissions' => ['lesson_results.view'],
        ]);
        $groups[__('Learning')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Available Courses'),
            'iconClass' => 'bi bi-book',
            'route' => route('courses.index'),
            'active' => Route::is('courses.index'),
            'id' => 'available_courses',
            'priority' => 10,
            'permissions' => [],
        ]);

        // My Courses
        $groups[__('Learning')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('My Courses'),
            'iconClass' => 'bi bi-journal-bookmark',
            'route' => route('student.my-courses'),
            'active' => Route::is('student.my-courses'),
            'id' => 'my_courses',
            'priority' => 20,
            'permissions' => [],
        ]);
        return $groups;
    }

    // Teacher Menus
    public function addTeacherMenus(array $groups): array
    {
        // Upcoming Lessons
        $groups[__('Teaching')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Upcoming Lessons'),
            'iconClass' => 'bi bi-calendar-check',
            'route' => route('teacher.upcoming-lessons'),
            'active' => Route::is('teacher.upcoming-lessons'),
            'id' => 'upcoming_lessons',
            'priority' => 10,
            'permissions' => ['lesson_results.view'],
        ]);

        // Lesson History
        $groups[__('Teaching')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Lesson History'),
            'iconClass' => 'bi bi-clock-history',
            'route' => route('teacher.lesson-history'),
            'active' => Route::is('teacher.lesson-history'),
            'id' => 'lesson_history',
            'priority' => 20,
            'permissions' => ['lesson_results.view'],
        ]);
        

        return $groups;
    }

    // Student Menus
    public function addStudentMenus(array $groups): array
    {
        // Available Courses
        $groups[__('Learning')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('Available Courses'),
            'iconClass' => 'bi bi-book',
            'route' => route('courses.index'),
            'active' => Route::is('courses.index'),
            'id' => 'available_courses',
            'priority' => 10,
            'permissions' => [],
        ]);

        // My Courses
        $groups[__('Learning')][] = (new AdminMenuItem())->setAttributes([
            'label' => __('My Courses'),
            'iconClass' => 'bi bi-journal-bookmark',
            'route' => route('student.my-courses'),
            'active' => Route::is('student.my-courses'),
            'id' => 'my_courses',
            'priority' => 20,
            'permissions' => [],
        ]);

        return $groups;
    }
}