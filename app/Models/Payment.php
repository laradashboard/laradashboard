<?php

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'status', 'user_course_id', 'type', 'discount_code','receipt_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class);
    }
}