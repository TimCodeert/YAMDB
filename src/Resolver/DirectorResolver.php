<?php

namespace App\Resolver;

use App\Repository\DirectorRepository;

class DirectorResolver
{
    private DirectorRepository $directorRepository;

    public function __construct(DirectorRepository $directorRepository)
    {
        $this->directorRepository = $directorRepository;
    }

    public function getDirectorLookupMap(array $domainMovies): array
    {
        if (empty($domainMovies)) {
            return [];
        }

        $directorNames = array_unique(
            array_map(fn($m) => $m->getDirector()->getName(), $domainMovies)
        );

        return $this->directorRepository->upsertDirectorsByNames($directorNames);
    }
}
