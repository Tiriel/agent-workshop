<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post_list')]
    public function index(PostRepository $repository): Response
    {
        $pager = new Pagerfanta(
            new QueryAdapter($repository->createQueryBuilder('p'))
        );

        return $this->render('post/index.html.twig', [
            'pager' => $pager,
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
