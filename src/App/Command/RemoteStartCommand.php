<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Command;

use Bveing\MBuddy\DevTools\ApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mbuddy:remote:start',
    description: 'Start remote server on iPad',
)]
class RemoteStartCommand extends Command
{
    public function __construct(
        private ApiClient $apiClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("⏳ Starting remote server");
        $this->apiClient->start();
        $output->writeln("✅ Remote server started");

        return Command::SUCCESS;
    }
}
