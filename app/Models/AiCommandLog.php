<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI Command Log Model
 *
 * Records all AI command executions for auditing and debugging.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $command
 * @property array|null $intent
 * @property array|null $plan
 * @property array|null $result
 * @property string $status
 * @property int|null $execution_time_ms
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User|null $user
 */
class AiCommandLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'command',
        'intent',
        'plan',
        'result',
        'status',
        'execution_time_ms',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'intent' => 'array',
            'plan' => 'array',
            'result' => 'array',
            'execution_time_ms' => 'integer',
        ];
    }

    /**
     * Get the user who executed the command.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful commands.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AiCommandLog>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AiCommandLog>
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed commands.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AiCommandLog>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AiCommandLog>
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if the command was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Get the execution time in seconds.
     */
    public function getExecutionTimeInSeconds(): ?float
    {
        if ($this->execution_time_ms === null) {
            return null;
        }

        return $this->execution_time_ms / 1000;
    }
}
