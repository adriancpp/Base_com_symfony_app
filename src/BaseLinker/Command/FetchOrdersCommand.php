<?php

declare(strict_types=1);

namespace App\BaseLinker\Command;

use App\BaseLinker\Exception\BaseLinkerApiException;
use App\BaseLinker\Service\OrdersService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'base_linker:orders:fetch',
    description: 'Fetch orders from BaseLinker (from given date, optional marketplace filter)',
)]
final class FetchOrdersCommand extends Command
{
    public function __construct(
        private readonly OrdersService $ordersService
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

        try {
            $orders = $this->ordersService->getOrders($dateFrom, orderSource: $source);
            $io->success(sprintf('Fetched %d order(s) from %s.', count($orders), $dateFrom->format('Y-m-d')));
            if (count($orders) > 0) {
                $io->table(
                    ['Order ID', 'Source', 'Date'],
                    array_map(
                        fn (array $o) => [
                            $o['order_id'] ?? '-',
                            $o['order_source'] ?? '-',
                            $this->formatOrderDate($o),
                        ],
                        array_slice($orders, 0, 10)
                    )
                );
                if (count($orders) > 10) {
                    $io->note(sprintf('Showing first 10 of %d. Max 100 per API call.', count($orders)));
                }
            }
            return Command::SUCCESS;
        } catch (BaseLinkerApiException $e) {
            $io->error('API error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function formatOrderDate(array $order): string
    {
        $ts = $order['date_confirmed'] ?? $order['date_add'] ?? null;
        return $ts !== null ? date('Y-m-d H:i', (int) $ts) : '-';
    }
}
