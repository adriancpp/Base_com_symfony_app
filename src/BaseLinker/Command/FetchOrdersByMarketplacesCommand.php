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
    name: 'base_linker:orders:fetch-by-marketplaces',
    description: 'Fetch orders from one or more marketplaces (e.g. allegro or allegro,ebay)',
)]
final class FetchOrdersByMarketplacesCommand extends Command
{
    public function __construct(
        private readonly OrdersService $ordersService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('marketplaces', 'm', InputOption::VALUE_REQUIRED, 'Comma-separated marketplace types (e.g. allegro,ebay)', 'allegro,ebay')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Fetch orders from last N days', 7);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $marketplaces = array_map('trim', explode(',', (string) $input->getOption('marketplaces')));
        $marketplaces = array_filter($marketplaces);

        if (count($marketplaces) < 1) {
            $io->error('Provide at least one marketplace (e.g. --marketplaces=allegro or --marketplaces=allegro,ebay).');
            return Command::FAILURE;
        }

        $days = (int) $input->getOption('days');
        $dateFrom = new \DateTimeImmutable("-{$days} days");

        try {
            $allOrders = $this->ordersService->getOrders($dateFrom);
            $allBySource = $this->groupOrdersBySource($allOrders, $marketplaces);

            $total = array_sum(array_map('count', $allBySource));
            $io->success(sprintf('Fetched %d order(s) from %d marketplace(s).', $total, count($marketplaces)));

            foreach ($allBySource as $source => $orders) {
                $io->section(sprintf('%s (%d orders)', $source, count($orders)));
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
                        $io->note(sprintf('Showing first 10 of %d.', count($orders)));
                    }
                } else {
                    $io->text('No orders.');
                }
            }

            return Command::SUCCESS;
        } catch (BaseLinkerApiException $e) {
            $io->error('API error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Group orders by order_source; keep only requested marketplaces.
     *
     * @param list<array<string, mixed>> $orders
     * @param list<string> $marketplaces
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupOrdersBySource(array $orders, array $marketplaces): array
    {
        $grouped = [];
        foreach ($marketplaces as $source) {
            $grouped[$source] = [];
        }

        foreach ($orders as $order) {
            $source = (string) ($order['order_source'] ?? '');
            if ($source !== '' && isset($grouped[$source])) {
                $grouped[$source][] = $order;
            }
        }

        return $grouped;
    }

    private function formatOrderDate(array $order): string
    {
        $ts = $order['date_confirmed'] ?? $order['date_add'] ?? null;
        return $ts !== null ? date('Y-m-d H:i', (int) $ts) : '-';
    }
}
