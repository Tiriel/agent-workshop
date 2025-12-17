<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/admin/tag')]
final class TagController extends AbstractAdminController
{
    #[Route(name: 'app_admin_tag_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->getIndex();
    }

    #[Route('/{id}', name: 'app_admin_tag_show', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function show(Tag $tag): Response
    {
        return $this->doShow($tag);
    }

    #[Route('/new', name: 'app_admin_tag_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_tag_edit', requirements: ['id' => Requirement::UUID], methods: ['GET', 'POST'])]
    public function save(Request $request, ?Tag $tag): Response
    {
        return $this->doSave(TagType::class, $request, $tag);
    }

    #[Route('/{id}', name: 'app_admin_tag_delete', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        return $this->doDelete($request, $tag);
    }

    protected function getShortName(): string
    {
        return 'tag';
    }

    protected function getEntityClass(): string
    {
        return Tag::class;
    }
}
