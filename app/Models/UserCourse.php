<?php

// app/Models/UserCourse.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'buy_date', 'lesson_day', 
        'lesson_hour', 'status', 'lesson_count','payment_receipt_path','payment_status', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonResults()
    {
        return $this->hasMany(LessonResult::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}