<?php

declare(strict_types=1);

namespace App\BaseLinker\Command;

use App\BaseLinker\Exception\BaseLinkerApiException;
use App\BaseLinker\Service\OrderSourcesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'base_linker:sources:list',
    description: 'List available order sources (marketplaces) from BaseLinker',
)]
final class ListSourcesCommand extends Command
{
    public function __construct(
        private readonly OrderSourcesService $orderSourcesService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $sources = $this->orderSourcesService->getSources();

            if (empty($sources)) {
                $io->warning('No order sources found.');
                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($sources as $type => $accounts) {
                if (!is_array($accounts)) {
                    continue;
                }
                $parts = [];
                foreach ($accounts as $id => $name) {
                    $parts[] = is_string($name) ? "{$id}: {$name}" : (string) $id;
                }
                $rows[] = [$type, implode(', ', $parts) ?: '-'];
            }

            $io->title('Order sources (marketplaces)');
            $io->table(['Type', 'Accounts (id: name)'], $rows);

            $types = $this->orderSourcesService->getMarketplaceTypes();
            $io->note(sprintf('Use --source=%s when fetching orders (e.g. base_linker:orders:fetch --source=allegro).', implode('|', array_slice($types, 0, 5))));

            return Command::SUCCESS;
        } catch (BaseLinkerApiException $e) {
            $io->error('API error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
