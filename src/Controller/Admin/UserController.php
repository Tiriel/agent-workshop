<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/admin/user')]
final class UserController extends AbstractAdminController
{
    #[Route(name: 'app_admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->getIndex();
    }

    #[Route('/{id}', name: 'app_admin_user_show', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->doShow($user);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_user_edit', requirements: ['id' => Requirement::UUID], methods: ['GET', 'POST'])]
    public function save(Request $request, ?User $user): Response
    {
        return $this->doSave(UserType::class, $request, $user);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        return $this->doDelete($request, $user);
    }

    protected function getShortName(): string
    {
        return 'user';
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }
}
