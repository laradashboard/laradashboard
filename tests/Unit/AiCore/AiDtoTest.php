<?php

declare(strict_types=1);

use App\Ai\Contracts\AiActionInterface;
use App\Ai\Data\AiIntent;
use App\Ai\Data\AiPlan;
use App\Ai\Data\AiResult;
use App\Ai\Data\AiStep;
use App\Ai\Registry\ActionRegistry;
use App\Ai\Registry\CapabilityRegistry;
use App\Ai\Registry\ContextRegistry;

beforeEach(function () {
    ActionRegistry::clear();
    CapabilityRegistry::clear();
    ContextRegistry::clear();
});

test('AiIntent can be created from array', function () {
    $data = [
        'intent' => 'create_content',
        'module' => 'posts',
        'goal' => 'Create a new post',
        'parameters' => ['topic' => 'Laravel'],
        'confidence' => 0.95,
    ];

    $intent = AiIntent::fromArray($data);

    expect($intent->intent)->toBe('create_content')
        ->and($intent->module)->toBe('posts')
        ->and($intent->goal)->toBe('Create a new post')
        ->and($intent->parameters)->toHaveKey('topic')
        ->and($intent->confidence)->toBe(0.95);
});

test('AiIntent toArray returns correct structure', function () {
    $intent = new AiIntent(
        intent: 'create_content',
        module: 'posts',
        goal: 'Create post',
        parameters: ['key' => 'value'],
        confidence: 0.9
    );

    $array = $intent->toArray();

    expect($array)->toHaveKey('intent')
        ->and($array)->toHaveKey('module')
        ->and($array)->toHaveKey('goal')
        ->and($array)->toHaveKey('parameters')
        ->and($array)->toHaveKey('confidence');
});

test('AiStep can be created from array', function () {
    $data = [
        'action' => 'posts.create',
        'payload' => ['topic' => 'Laravel'],
        'description' => 'Create a post',
    ];

    $step = AiStep::fromArray($data, 0);

    expect($step->action)->toBe('posts.create')
        ->and($step->payload)->toHaveKey('topic')
        ->and($step->description)->toBe('Create a post')
        ->and($step->order)->toBe(0);
});

test('AiPlan can be created from array with steps', function () {
    $data = [
        'intent' => [
            'intent' => 'create_content',
            'module' => 'posts',
            'goal' => 'Create post',
        ],
        'steps' => [
            ['action' => 'posts.create', 'payload' => ['topic' => 'Test'], 'description' => 'Create post'],
        ],
        'summary' => 'Creating a post',
    ];

    $plan = AiPlan::fromArray($data);

    expect($plan->intent->intent)->toBe('create_content')
        ->and($plan->steps)->toHaveCount(1)
        ->and($plan->steps[0]->action)->toBe('posts.create')
        ->and($plan->summary)->toBe('Creating a post')
        ->and($plan->stepCount())->toBe(1);
});

test('AiResult can be created with success status', function () {
    $result = AiResult::success(
        message: 'Post created',
        data: ['post_id' => 1],
        actions: ['Edit' => '/posts/1/edit'],
        completedSteps: ['Created post']
    );

    expect($result->status)->toBe('success')
        ->and($result->message)->toBe('Post created')
        ->and($result->data)->toHaveKey('post_id')
        ->and($result->actions)->toHaveKey('Edit')
        ->and($result->isSuccess())->toBeTrue()
        ->and($result->isFailed())->toBeFalse();
});

test('AiResult can be created with failed status', function () {
    $result = AiResult::failed('Something went wrong');

    expect($result->status)->toBe('failed')
        ->and($result->message)->toBe('Something went wrong')
        ->and($result->isSuccess())->toBeFalse()
        ->and($result->isFailed())->toBeTrue();
});

test('AiResult can be created with partial status', function () {
    $result = AiResult::partial(
        message: 'Partially completed',
        data: ['partial' => true],
        completedSteps: ['Step 1']
    );

    expect($result->status)->toBe('partial')
        ->and($result->isSuccess())->toBeFalse()
        ->and($result->isFailed())->toBeFalse();
});

test('AiResult toArray returns correct structure', function () {
    $result = AiResult::success('Done', ['key' => 'value']);
    $array = $result->toArray();

    expect($array)->toHaveKey('status')
        ->and($array)->toHaveKey('message')
        ->and($array)->toHaveKey('data')
        ->and($array)->toHaveKey('actions')
        ->and($array)->toHaveKey('completed_steps');
});

test('ActionRegistry can register and resolve actions', function () {
    $mockAction = new class () implements AiActionInterface {
        public static function name(): string
        {
            return 'test.action';
        }

        public static function description(): string
        {
            return 'A test action';
        }

        public static function payloadSchema(): array
        {
            return ['type' => 'object'];
        }

        public static function permission(): ?string
        {
            return null;
        }

        public function handle(array $payload): AiResult
        {
            return AiResult::success('Test completed');
        }
    };

    ActionRegistry::register($mockAction::class);

    expect(ActionRegistry::has('test.action'))->toBeTrue()
        ->and(ActionRegistry::has('unknown.action'))->toBeFalse();

    $resolved = ActionRegistry::resolve('test.action');
    expect($resolved)->toBeInstanceOf(AiActionInterface::class);
});

test('ActionRegistry getActionsForAi returns formatted action list', function () {
    $mockAction = new class () implements AiActionInterface {
        public static function name(): string
        {
            return 'test.action';
        }

        public static function description(): string
        {
            return 'A test action';
        }

        public static function payloadSchema(): array
        {
            return ['type' => 'object'];
        }

        public static function permission(): ?string
        {
            return 'test.permission';
        }

        public function handle(array $payload): AiResult
        {
            return AiResult::success('Done');
        }
    };

    ActionRegistry::register($mockAction::class);
    $actions = ActionRegistry::getActionsForAi();

    expect($actions)->toHaveCount(1)
        ->and($actions[0])->toHaveKey('name')
        ->and($actions[0])->toHaveKey('description')
        ->and($actions[0])->toHaveKey('schema')
        ->and($actions[0]['name'])->toBe('test.action');
});

test('ActionRegistry all returns all registered actions', function () {
    ActionRegistry::clear();

    expect(ActionRegistry::all())->toBeEmpty();
});
