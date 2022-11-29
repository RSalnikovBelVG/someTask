<?php

namespace App\Tests\unit\Bundle\TestBundle\Messenger\Handler;

use App\Bundle\TestBundle\Messenger\Handler\ExchangeHandler;
use App\Bundle\TestBundle\Messenger\Message\ExchangeMessage;
use App\Bundle\TestBundle\Services\BinService;
use App\Bundle\TestBundle\Services\CalculateService;
use App\Bundle\TestBundle\Services\RatesService;
use Codeception\Test\Unit;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ExchangeHandlerTest extends Unit
{

    private $binService;
    private $ratesService;

    public function testExchange()
    {
        $exchangeHandler = new ExchangeHandler(new CalculateService([
            'bins_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[country][alpha2]'
            ],
            'rates_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[rates]',
                'currency' => 'USD'
            ]
        ]));
        $this->binService->method('getData')->willReturn(['country' => ['alpha2' => 'DK']]);
        $this->ratesService->method('getData')->willReturn(['rates' => ['USD' => 1, 'EUR' => 1.5]]);

        $exchangeHandler->setBinService($this->binService);
        $exchangeHandler->setRatesService($this->ratesService);
        $exchangeMessage = new ExchangeMessage();
        $exchangeMessage->setFileName('./input.txt');

        self::assertEquals([0.67, 0.50], $exchangeHandler($exchangeMessage));
    }

    public function testExchangeFileNotFound()
    {
        $this->expectException(FileNotFoundException::class);

        $exchangeHandler = new ExchangeHandler(new CalculateService([
            'bins_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[country][alpha2]'
            ],
            'rates_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[rates]',
                'currency' => 'USD'
            ]
        ]));
        $this->binService->method('getData')->willReturn(['country' => ['alpha2' => 'DK']]);
        $this->ratesService->method('getData')->willReturn(['rates' => ['USD' => 1, 'EUR' => 1.5]]);

        $exchangeHandler->setBinService($this->binService);
        $exchangeHandler->setRatesService($this->ratesService);
        $exchangeMessage = new ExchangeMessage();
        $exchangeMessage->setFileName('./input.txt1');
        $exchangeHandler($exchangeMessage);
    }

    public function testExchangePropertyNotFound()
    {
        $this->expectException(NoSuchPropertyException::class);

        $exchangeHandler = new ExchangeHandler(new CalculateService([
            'bins_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[country][alpha2]'
            ],
            'rates_provider' => [
                'url' => '',
                'auth' => '',
                'mapping' => '[rates1]',
                'currency' => 'USD'
            ]
        ]));
        $this->binService->method('getData')->willReturn(['country' => ['alpha2' => 'DK']]);
        $this->ratesService->method('getData')->willReturn(['rates' => ['USD' => 1, 'EUR' => 1.5]]);

        $exchangeHandler->setBinService($this->binService);
        $exchangeHandler->setRatesService($this->ratesService);
        $exchangeMessage = new ExchangeMessage();
        $exchangeMessage->setFileName('./input.txt');
        $exchangeHandler($exchangeMessage);
    }


    protected function setUp(): void
    {
        $this->binService = $this->createMock(BinService::class);
        $this->ratesService = $this->createMock(RatesService::class);
    }

    protected function tearDown(): void
    {
        $this->binService = null;
        $this->ratesService = null;
    }

}
