<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProfileCard extends Component
{
    /**
     * The name to display.
     *
     * @var string
     */
    public string $name;

    /**
     * The image URL.
     *
     * @var string|null
     */
    public ?string $imageUrl;

    /**
     * The subtitle text.
     *
     * @var string|null
     */
    public ?string $subtitle;

    /**
     * Extra information to display.
     *
     * @var string|null
     */
    public ?string $extraInfo;

    /**
     * Optional link for the card.
     *
     * @var string|null
     */
    public ?string $link;

    /**
     * Size of the profile card.
     *
     * @var string
     */
    public string $size;

    /**
     * Tooltip title.
     *
     * @var string|null
     */
    public ?string $tooltipTitle;

    /**
     * Tooltip ID for targeting.
     *
     * @var string|null
     */
    public ?string $tooltipId;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string $name = '',
        ?string $imageUrl = null,
        ?string $subtitle = null,
        ?string $extraInfo = null,
        ?string $link = null,
        string $size = 'md',
        ?string $tooltipTitle = null,
        ?string $tooltipId = null
    ) {
        $this->name = $name;
        $this->imageUrl = $imageUrl;
        $this->subtitle = $subtitle;
        $this->extraInfo = $extraInfo;
        $this->link = $link;
        $this->size = $size;
        $this->tooltipTitle = $tooltipTitle;
        $this->tooltipId = $tooltipId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.profile-card');
    }
}
