<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CountryService;
use PHPUnit\Framework\TestCase;

class CountryServiceTest extends TestCase
{
    public function test_get_countries_returns_country_list()
    {
        $service = new CountryService();
        $countries = $service->getCountries();

        $this->assertIsArray($countries);
        $this->assertNotEmpty($countries);
        $this->assertContains(['value' => 'BD', 'label' => 'Bangladesh'], $countries);
        $this->assertContains(['value' => 'US', 'label' => 'United States'], $countries);
        $this->assertContains(['value' => 'IN', 'label' => 'India'], $countries);
    }
}
