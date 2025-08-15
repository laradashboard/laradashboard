<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\Course::create([
            'title' => 'Introduction to Programming',
            'description' => 'Learn the basics of programming with this introductory course.',
            'price' => 199.99,
            'ebook_file_path' => null,
            'lesson_count' => 10,
        ]);

        \App\Models\Course::create([
            'title' => 'Web Development Fundamentals',
            'description' => 'Build your first website with HTML, CSS, and JavaScript.',
            'price' => 249.99,
            'ebook_file_path' => null,
            'lesson_count' => 12,
        ]);

        \App\Models\Course::create([
            'title' => 'Advanced Laravel',
            'description' => 'Take your Laravel skills to the next level with advanced techniques.',
            'price' => 349.99,
            'ebook_file_path' => null,
            'lesson_count' => 15,
        ]);
    }
}
