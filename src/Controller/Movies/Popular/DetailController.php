<?php


namespace App\Controller\Movies\Popular;

use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DetailController extends AbstractController
{
    #[Route('/movies/{id}', name: 'movie_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->findOneWithDirector($id);

        if (!$movie) {
            throw new NotFoundHttpException('Movie not found.');
        }

        return $this->render('movies/popular/detail.html.twig', [
            'movie' => $movie,
        ]);
    }
}
