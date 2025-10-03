<?php

namespace App\Model;

use DateTimeImmutable;

class Movie
{
    private string $title;
    private Director $director;
    private DateTimeImmutable $releaseDate;
    private string $description;

    public function __construct($title, $director, $releaseDate, $description)
    {
        $this->title = $title;
        $this->director = $director;
        $this->releaseDate = $releaseDate;
        $this->description = $description;
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
