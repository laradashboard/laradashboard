<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];
}
