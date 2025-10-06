<?php

namespace App\Command;

use App\Service\MovieSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-popular-movies',
    description: 'Sync all popular movies',
)]
class SyncPopularMoviesCommand extends Command
{
    private MovieSyncService $movieSyncService;
    public function __construct(MovieSyncService $movieSyncService)
    {
        parent::__construct();
        $this->movieSyncService = $movieSyncService;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Starting synchronization...');
        $startTime = microtime(true);
        $this->movieSyncService->syncMovies($output);
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $output->writeln("\n");
        $io->success(sprintf("Done in %s seconds.", intval($duration)));

        return Command::SUCCESS;
    }
}
