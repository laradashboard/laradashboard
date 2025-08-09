<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification;
use App\Concerns\AuthorizationChecker;
use App\Concerns\HasGravatar;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Auth\Notifications\ResetPassword as DefaultResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use AuthorizationChecker;
    use HasApiTokens;
    use HasFactory;
    use HasGravatar;
    use HasRoles;
    use Notifiable;
    use QueryBuilderTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class, 'action_by');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        // Check if the request is for the admin panel
        if (request()->is('admin/*')) {
            $this->notify(new AdminResetPasswordNotification($token));
        } else {
            $this->notify(new DefaultResetPassword($token));
        }
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param  array|string  $permissions
     */
    public function hasAnyPermission($permissions): bool
    {
        if (empty($permissions)) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get searchable columns for the model.
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'email', 'username'];
    }

    /**
     * Get columns that should be excluded from sorting.
     */
    protected function getExcludedSortColumns(): array
    {
        return [];
    }
}
