<?php

namespace App\Service;

use App\Mapper\DirectorMapper;
use App\Mapper\MovieMapper;
use App\Model\MovieCollection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    CONST LANGUAGE = 'en-US';
    private string $apiKey;
    private string $baseUrl;
    private string $version;
    private HttpClientInterface $client;
    private MovieMapper $movieMapper;
    private LoggerInterface $logger;

    public function __construct(
        string $tmdbApiKey,
        string $baseUrl,
        int $version,
        HttpClientInterface $client,
        MovieMapper $movieMapper,
        LoggerInterface $logger,
    )
    {
        $this->apiKey = $tmdbApiKey;
        $this->baseUrl = $baseUrl;
        $this->version = $version;
        $this->client = $client;
        $this->movieMapper = $movieMapper;
        $this->logger = $logger;
    }

    public function getPopularMovies(int $page = 1): MovieCollection
    {
        $url = $this->getFullUrl('movie/popular');
        $response = $this->fetchFromTmdb($url, $page);
        $credits = $this->fetchCredits($response['results']);
        $directors = $this->resolveCredits($credits);

        return $this->movieMapper->mapPopularMovies($response['results'], $directors);
    }

    public function getLimitPopularMovies(): int
    {
        $url = $this->getFullUrl('movie/popular');
        return $this->fetchFromTmdb($url)['total_pages'];
    }

    private function fetchCredits(array $movieDataList): array
    {
        $requests = [];
        foreach ($movieDataList as $movieData) {
            $movieId = (int)$movieData['id'];
            $creditsUrl = $this->getFullUrl("movie/{$movieId}/credits");

            $requests[$movieId] = $this->client->request('GET', $creditsUrl,
                [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'accept' => 'application/json',
                ],
                'query' => ['language' => self::LANGUAGE],
            ]);
        }
        return $requests;
    }

    private function resolveCredits(array $requests): array
    {
        $directors = [];
        foreach ($requests as $movieId => $responseObject) {
            try {
                $credits = $responseObject->toArray();
                $directors[$movieId] = $this->getDirector($credits);
            } catch (\Exception $e) {
                $this->logger->warning(sprintf('Failed to fetch credits for movie ID %d: %s', $movieId, $e->getMessage()));
                $directors[$movieId] = 'Error fetching director';
            }
        }
        return $directors;
    }

    private function getDirector(array $credits): string
    {
        $director = array_find($credits['crew'], fn($m) => $m['job'] === 'Director');
        return $director ? $director['name'] : 'No director found';
    }

    private function getFullUrl(string $route): string
    {
        return sprintf('%s/%s/%s', $this->baseUrl, $this->version, $route);
    }

    private function fetchFromTmdb(string $url, $page = 1): array
    {
        try {
            return $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'accept' => 'application/json',
                ],
                'query' => [
                    'language' => self::LANGUAGE,
                    'page' => $page,
                ],
            ])->toArray();
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('Failed to fetch movies for page %d: %s', $page, $e->getMessage()));
            return [];
        }

    }
}
