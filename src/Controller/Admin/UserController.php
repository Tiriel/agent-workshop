<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function save(Request $request, ?User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user ??= new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            /** @var EntityManagerInterface $manager */
            $manager = $this->container->get('manager');
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render("admin/crud/save.html.twig", [
            'entity' => $user,
            'form' => $form,
        ]);
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
