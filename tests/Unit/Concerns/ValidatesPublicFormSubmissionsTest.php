<?php

declare(strict_types=1);

use App\Concerns\ValidatesPublicFormSubmissions;
use Livewire\Component;

uses()->group('public-form');

test('collect public form email values reads direct livewire properties', function () {
    $component = new class () extends Component {
        use ValidatesPublicFormSubmissions;

        public string $customer_email = 'testing-wrong-email@testinang.com';

        public function render(): string
        {
            return '<div></div>';
        }
    };

    $method = new ReflectionMethod($component, 'collectPublicFormEmailValues');
    $method->setAccessible(true);

    expect($method->invoke($component, ['customer_email']))
        ->toBe(['testing-wrong-email@testinang.com']);
});

test('collect public form email values reads nested form data fields', function () {
    $component = new class () extends Component {
        use ValidatesPublicFormSubmissions;

        /** @var array<string, mixed> */
        public array $formData = ['email' => 'user@example.com'];

        public function render(): string
        {
            return '<div></div>';
        }
    };

    $method = new ReflectionMethod($component, 'collectPublicFormEmailValues');
    $method->setAccessible(true);

    expect($method->invoke($component, ['formData.email']))
        ->toBe(['user@example.com']);
});
