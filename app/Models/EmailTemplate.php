<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TemplateType;
use App\Concerns\QueryBuilderTrait;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'subject',
        'body_html',
        'body_text',
        'type',
        'variables',
        'preview_image',
        'description',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
        'header_template_id',
        'footer_template_id',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'type' => TemplateType::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    public function headerTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'header_template_id');
    }

    public function footerTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'footer_template_id');
    }

    public function renderTemplate(array $data = []): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyHtml = str_replace($placeholder, $value, $bodyHtml ?? '');
            $bodyText = str_replace($placeholder, $value, $bodyText ?? '');
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }

    public function extractVariables(): array
    {
        $content = $this->subject . ' ' . $this->body_html . ' ' . $this->body_text;
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);

        return array_unique($matches[1]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, TemplateType $type)
    {
        return $query->where('type', $type);
    }

    public static function getAvailableVariables(): array
    {
        return [
            ['label' => 'First Name', 'value' => '{{ first_name }}'],
            ['label' => 'Last Name', 'value' => '{{ last_name }}'],
            ['label' => 'Full Name', 'value' => '{{ full_name }}'],
            ['label' => 'Company', 'value' => '{{ company }}'],
            ['label' => 'Email', 'value' => '{{ email }}'],
            ['label' => 'Phone', 'value' => '{{ phone }}'],
            ['label' => 'Job Title', 'value' => '{{ job_title }}'],
            ['label' => 'Website', 'value' => '{{ website }}'],
            ['label' => 'Industry', 'value' => '{{ industry }}'],
            ['label' => 'Activity Title', 'value' => '{{ activity_title }}'],
            ['label' => 'Activity Description', 'value' => '{{ activity_description }}'],
            ['label' => 'Due Date', 'value' => '{{ due_date }}'],
        ];
    }
}
