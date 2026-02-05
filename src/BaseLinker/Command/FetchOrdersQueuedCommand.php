<?php

declare(strict_types=1);

namespace App\BaseLinker\Command;

use App\BaseLinker\Message\FetchOrdersMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'base_linker:orders:fetch-queued',
    description: 'Dispatch fetch-orders job to the queue (sync by default; handler logs result)',
)]
final class FetchOrdersQueuedCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Fetch orders from last N days', 7)
            ->addOption('source', 's', InputOption::VALUE_OPTIONAL, 'Filter by order source (e.g. allegro, ebay)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = (int) $input->getOption('days');
        $source = $input->getOption('source');

        $dateFrom = new \DateTimeImmutable("-{$days} days");

        $this->messageBus->dispatch(new FetchOrdersMessage(
            $dateFrom->getTimestamp(),
            null,
            $source !== null && $source !== '' ? $source : null,
            false
        ));

        $io->success(sprintf('Dispatched FetchOrdersMessage (from %s). Handler ran via sync transport; check logs for result.', $dateFrom->format('Y-m-d')));

        return Command::SUCCESS;
    }
}
