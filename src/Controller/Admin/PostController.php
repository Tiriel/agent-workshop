<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/admin/post')]
final class PostController extends AbstractAdminController
{
    #[Route(name: 'app_admin_post_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->getIndex();
    }

    #[Route('/{id}', name: 'app_admin_post_show', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->doShow($post);
    }

    #[Route('/new', name: 'app_admin_post_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_post_edit', requirements: ['id' => Requirement::UUID], methods: ['GET', 'POST'])]
    public function save(Request $request, ?Post $post): Response
    {
        return $this->doSave(PostType::class, $request, $post);
    }

    #[Route('/{id}', name: 'app_admin_post_delete', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    public function delete(Request $request, Post $post): Response
    {
        return $this->doDelete($request, $post);
    }

    #[Route('/{id}/{transitionName}', name: 'app_admin_post_transition', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    public function transition(Request $request, Post $post, string $transitionName, WorkflowInterface $postLifecycleStateMachine): Response
    {
        $entityManager = $this->container->get('manager');

        if (
            $this->isCsrfTokenValid('transition_'.$transitionName.$post->getId(), $request->getPayload()->getString('_token'))
            && $postLifecycleStateMachine->can($post, $transitionName)
        ) {
            $postLifecycleStateMachine->apply($post, $transitionName);
            $post->setUpdatedAt(Clock::get()->now());
            $entityManager->persist($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute(sprintf("app_admin_%s_show", $this->getShortName()), ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }

    protected function getShortName(): string
    {
        return 'post';
    }

    protected function getEntityClass(): string
    {
        return Post::class;
    }
}
