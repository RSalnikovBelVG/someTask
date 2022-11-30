<?php

namespace App\Tests\unit\Bundle\TestBundle\Services;

use App\Bundle\TestBundle\Services\CalculateService;
use PHPUnit\Framework\TestCase;

class CalculateServiceTest extends TestCase
{

    public function testIsCountryEU()
    {
        self::assertTrue(CalculateService::isCountryEU('AT'));
    }

    public function testIsNotCountryEU()
    {
        self::assertFalse(CalculateService::isCountryEU('WWW'));
    }

}
