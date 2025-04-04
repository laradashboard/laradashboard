<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLogModel extends Model
{
    protected $fillable = [
        'type',
        'action_by',
        'title',
        'data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
