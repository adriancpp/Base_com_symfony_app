<?php

declare(strict_types=1);

namespace App\BaseLinker\Command;

use App\BaseLinker\Client\BaseLinkerClient;
use App\BaseLinker\Exception\BaseLinkerApiException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'base_linker:test-connection',
    description: 'Test BaseLinker API connection (calls getOrderStatusList)',
)]
final class TestConnectionCommand extends Command
{
    public function __construct(
        private readonly BaseLinkerClient $client
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->client->request('getOrderStatusList', []);
            $io->success('BaseLinker API connection OK.');
            return Command::SUCCESS;
        } catch (BaseLinkerApiException $e) {
            $io->error('API error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
