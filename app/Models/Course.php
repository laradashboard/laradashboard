<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'price', 'ebook_file_path', 'lesson_count'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_courses')
            ->withPivot(['buy_date', 'lesson_day', 'lesson_hour', 'status', 'lesson_count'])
            ->withTimestamps();
    }

    public function userCourses()
    {
        return $this->hasMany(UserCourse::class);
    }
}