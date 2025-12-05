<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('', name: 'app_home_index')]
    public function index(PostRepository $repository): Response
    {
        return $this->render('home/index.html.twig', [
            'last_posts' => $repository->findBy([], ['publishedAt' => 'DESC'], 5),
        ]);
    }
}
