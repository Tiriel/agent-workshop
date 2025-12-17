<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

abstract class AbstractAdminController extends AbstractController
{
    abstract protected function getShortName(): string;
    abstract protected function getEntityClass(): string;

    protected function getIndex(): Response
    {
        return $this->render("admin/crud/index.html.twig", [
            'componentName' => ucfirst($this->getShortName()).'List',
        ]);
    }

    protected function doSave(string $formType, Request $request, ?object $entity = null): Response
    {
        $entity ??= new ($this->getEntityClass())();
        $form = $this->createForm($formType, $entity);
        $form->handleRequest($request);
        $entityManager = $this->container->get('manager');


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entity);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render("admin/crud/save.html.twig", [
            'entity' => $entity,
            'form' => $form,
        ]);
    }

    protected function doShow(object $entity): Response
    {
        return $this->render("admin/crud/show.html.twig", [
            'entity' => $entity,
            'componentName' => 'Show'.ucfirst($this->getShortName()),
        ]);
    }

    protected function doDelete(Request $request, object $entity): Response
    {
        $entityManager = $this->container->get('manager');

        if ($this->isCsrfTokenValid('delete'.$entity->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($entity);
            $entityManager->flush();
        }

        return $this->redirectToRoute(sprintf("app_admin_%s_index", $this->getShortName()), [], Response::HTTP_SEE_OTHER);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $parameters['shortName'] = $this->getShortName();

        return parent::render($view, $parameters, $response);
    }

    public static function getSubscribedServices(): array
    {
        return \array_unique(\array_merge(
            ['manager' => '?'.EntityManagerInterface::class],
            parent::getSubscribedServices(),
        ));
    }
}
