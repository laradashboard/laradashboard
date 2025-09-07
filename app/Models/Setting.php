<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

#[ObservedBy([SettingObserver::class])]
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            // Clear config cache when a setting is saved
            Artisan::call('config:clear');
        });

        static::deleted(function ($setting) {
            // Clear config cache when a setting is deleted
            Artisan::call('config:clear');
        });
    }
}
