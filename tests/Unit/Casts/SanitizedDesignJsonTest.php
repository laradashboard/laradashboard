<?php

declare(strict_types=1);

use App\Models\EmailTemplate;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

dataset('design json models', [
    // Closures so the models are built after the application has booted.
    'post' => fn () => new Post(),
    'email template' => fn () => new EmailTemplate(),
]);

function designJsonXssPayload(): array
{
    return [
        'blocks' => [
            ['id' => 'b1', 'type' => 'text', 'props' => ['content' => 'Hi <img src=x onerror=alert(1)>']],
        ],
    ];
}

test('design_json is sanitized before it is stored', function (Model $model) {
    $model->design_json = designJsonXssPayload();

    $stored = $model->getAttributes()['design_json'];

    expect($stored)->not->toContain('onerror')
        ->and(json_decode($stored, true)['blocks'][0]['props']['content'])->toBe('Hi <img src=x>');
})->with('design json models');

test('design_json stored before the fix is sanitized when read, e.g. by the builder', function (Model $model) {
    $model->setRawAttributes(['design_json' => json_encode(designJsonXssPayload())]);

    expect($model->design_json['blocks'][0]['props']['content'])->toBe('Hi <img src=x>');
})->with('design json models');

test('null and invalid design_json stay null', function () {
    $post = new Post();
    $post->design_json = null;
    expect($post->design_json)->toBeNull();

    $post->setRawAttributes(['design_json' => 'not json']);
    expect($post->design_json)->toBeNull();
});
