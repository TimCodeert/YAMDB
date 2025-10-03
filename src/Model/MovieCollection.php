<?php

namespace App\Model;

class MovieCollection implements \IteratorAggregate, \Countable
{
    private array $movies = [];

    public function __construct(array $movies = [])
    {
        $this->movies = $movies;
    }

    public function addMovie(Movie $movie): void
    {
        $this->movies[] = $movie;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->movies);
    }

    public function count(): int
    {
        return count($this->movies);
    }

    public function all(): array
    {
        return $this->movies;
    }
}
