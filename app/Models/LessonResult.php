<?php

// app/Models/LessonResult.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_course_id', 'user_id', 'status', 
        'teacher_note', 'course_date', 'update_date'
    ];

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class);
    }
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}