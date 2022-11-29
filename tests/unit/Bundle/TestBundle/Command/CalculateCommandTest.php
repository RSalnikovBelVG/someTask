<?php

namespace App\Tests\unit\Bundle\TestBundle\Command;

use App\Bundle\TestBundle\Command\CalculateCommand;
use App\Bundle\TestBundle\Messenger\Handler\ExchangeHandler;
use App\Bundle\TestBundle\Messenger\Message\ExchangeMessage;
use Codeception\Test\Unit;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class CalculateCommandTest extends Unit
{

    private $messageBus;
    private $commandTester;

    public function testExecute()
    {
        $this->messageBus->method('dispatch')->willReturn(new Envelope(new ExchangeMessage(),
            [new HandledStamp([], ExchangeHandler::class)]));
        $status = $this->commandTester->execute(['file' => './input.txt']);
        static::assertEquals(0, $status);
    }

    public function testExecuteFail()
    {
        $this->messageBus->method('dispatch')->willThrowException(new HandlerFailedException(new Envelope(new ExchangeMessage()), [new Exception()]));
        $status = $this->commandTester->execute(['file' => './input.txt']);
        static::assertEquals(1, $status);
    }

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $app = new Application();
        $app->add(new CalculateCommand($this->messageBus));
        $calculate = $app->find('calculate');
        $this->commandTester = new CommandTester($calculate);
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
        $this->messageBus = null;
    }

}
