<?php

namespace App\Service;

use App\Mapper\MovieMapper;
use App\Resolver\DirectorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class MovieSyncService
{
    CONST PAGE_LIMIT = 500;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private MovieMapper $mapper;
    private TmdbService $tmdbService;
    private DirectorResolver $directorResolver;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $tmdbLogger,
        MovieMapper $mapper,
        TmdbService $tmdbService,
        DirectorResolver $directorResolver,
    )
    {
        $this->em = $em;
        $this->logger = $tmdbLogger;
        $this->mapper = $mapper;
        $this->tmdbService = $tmdbService;
        $this->directorResolver = $directorResolver;
    }

    public function syncMovies(OutputInterface $output): void
    {
        $page = 1;
        $limit = min($this->tmdbService->getLimitPopularMovies(), self::PAGE_LIMIT);
        $externalIds = [];

        $progressBar = new ProgressBar($output, $limit);
        $progressBar->start();

        // API always returns unfiltered data, so it's faster to insert movie data instead of upsert
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE movie');
        while ($page <= $limit) {
            // Get movie data from API, already mapped into MovieCollection
            $movies = $this->tmdbService->getPopularMovies($page);
            // Upsert directors from movies, create lookup for later insertion
            $directors = $this->directorResolver->getDirectorLookupMap($movies->all());

            foreach ($movies as $movie) {
                // API returns duplicates
                if (isset($externalIds[$movie->getExternalId()])) {
                    $this->logger->info(
                        sprintf('Movie was already imported: %s (already present on page %s but also found on %s)',
                            $movie->getTitle(),
                            $externalIds[$movie->getExternalId()],
                            $page,
                        )
                    );
                    continue;
                }
                $externalIds[$movie->getExternalId()] = $page;

                // Map to DB entity and persist
                $directorName = $movie->getDirector()->getName();
                $directorEntity = $directors[$directorName] ?? null;
                $movieEntity = $this->mapper->mapToEntity($movie, $directorEntity);
                $this->em->persist($movieEntity);
            }

            $this->em->flush();
            $this->em->clear();

            $this->logger->info(
                'Synced popular movies page',
                [
                    'page' => $page,
                ]
            );

            // API returns broken pages
            $count = count($movies);

            $progressBar->setMessage(sprintf('Processing page %d (%d movies)', $page, $count));
            $progressBar->advance();
            if ($count < 20) {
                $this->logger->info(
                    'Page returned less movies than 20',
                    [
                        'page' => $page,
                        'movies' => $count,
                    ]
                );
            }

            $page++;
        }
        $progressBar->finish();
    }
}
