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
        'lesson_hour', 'status', 'lesson_count','payment_receipt_path','payment_status', 'teacher_id'
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

    public function getStatusBadgeAttribute()
    {
        return [
            'pending_payment' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-blue-100 text-blue-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}