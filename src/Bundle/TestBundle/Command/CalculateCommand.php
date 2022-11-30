<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Command;

use App\Bundle\TestBundle\Messenger\Message\ExchangeMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class CalculateCommand extends Command
{

    use HandleTrait;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus, string $name = null)
    {
        $this->setName('calculate');
        $this->messageBus = $messageBus;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exchangeMessage = new ExchangeMessage();
        $exchangeMessage->setFileName($input->getArgument('file'));

        try {
            $busResults = $this->handle(Envelope::wrap($exchangeMessage));
        } catch (HandlerFailedException|NoSuchPropertyException $exception) {
            $output->writeln('<error>Wrong mapping or properties</error>');
            return Command::FAILURE;
        }

        foreach ($busResults as $result) {
            $output->writeln("<fg=green>$result</>");
        }

        return Command::SUCCESS;
    }
}