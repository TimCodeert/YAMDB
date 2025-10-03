<?php

namespace App\Controller\Movies\Popular;

use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListController extends AbstractController
{
    #[Route('/', name: 'movie_list')]
    public function list(Request $request, MovieRepository $repository, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q', '');
        $query = $repository->createPaginationQuery($searchQuery);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('movies/popular/list.html.twig', [
            'pagination' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }
}
