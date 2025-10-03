<?php

namespace App\Service;

use App\Mapper\MovieMapper;
use App\Repository\DirectorRepository;
use App\Resolver\DirectorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

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

    public function syncMovies(): void
    {
        $page = 1;
        $limit = min($this->tmdbService->getLimitPopularMovies(), self::PAGE_LIMIT);
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE movie');
        $externalIds = [];
        while ($page <= $limit) {
            $movies = $this->tmdbService->getPopularMovies($page);
            $directors = $this->directorResolver->getDirectorLookupMap($movies->all());

            foreach ($movies as $movie) {

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

                $directorName = $movie->getDirector()->getName();
                $directorEntity = $directors[$directorName] ?? null;

                if ($directorEntity === null) {
                    $this->logger->error(sprintf('Director entity not found for name: %s', $directorName));
                    continue;
                }

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

            $count = count($movies);
            if ($count < 20) {
                $this->logger->warning(
                    'Page returned less movies than 20',
                    [
                        'page' => $page,
                        'movies' => $count,
                    ]
                );
            }

            $page++;
        }
    }
}
