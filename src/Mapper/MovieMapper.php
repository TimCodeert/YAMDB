<?php

namespace App\Mapper;


use App\Entity\Director;
use App\Model\MovieCollection;
use App\Model\Movie as DomainMovie;
use App\Entity\Movie;
use DateTimeImmutable;

class MovieMapper
{
    private DirectorMapper $directorMapper;
    public function __construct(DirectorMapper $directorMapper)
    {
        $this->directorMapper = $directorMapper;
    }
    public function mapToEntity(DomainMovie $domainMovie, Director $director): Movie {

        $movie = new Movie();
        $movie->setTitle($domainMovie->getTitle());
        $movie->setDescription($domainMovie->getDescription());
        $movie->setDirector($director);
        $movie->setReleaseDate($domainMovie->getReleaseDate());

        return $movie;
    }

    public function mapPopularMovies(array $moviesData, array $directors = []): MovieCollection
    {
        $collection = new MovieCollection();

        foreach ($moviesData as $movieData) {
            $externalId = $movieData['id'];
            $title = $movieData['title'];
            $director = $this->directorMapper->mapToDomain($directors[$externalId]);
            $releaseDate = new DateTimeImmutable($movieData['release_date']);
            $description = $movieData['overview'];
            $collection->addMovie(new DomainMovie($externalId, $title, $director, $releaseDate, $description));
        }

        return $collection;
    }
}
