<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;

class MediaModal extends Component
{
    public function __construct(
        public string $id = 'mediaModal',
        public string $title = 'Select Media',
        public bool $multiple = false,
        public string $allowedTypes = 'all',
        public ?string $onSelect = null,
        public string $buttonText = 'Select Media',
        public string $buttonClass = 'btn-primary'
    ) {
    }

    public function render()
    {
        return view('components.media-modal');
    }
}