<?php

namespace App\Model;

use DateTimeImmutable;

class Movie
{
    private int $externalId;
    private string $title;
    private Director $director;
    private DateTimeImmutable $releaseDate;
    private string $description;

    public function __construct($externalId, $title, $director, $releaseDate, $description)
    {
        $this->externalId = $externalId;
        $this->title = $title;
        $this->director = $director;
        $this->releaseDate = $releaseDate;
        $this->description = $description;
    }

    public function getExternalId(): int
    {
        return $this->externalId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDirector(): Director
    {
        return $this->director;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

}
