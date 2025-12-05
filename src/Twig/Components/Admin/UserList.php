<?php

namespace App\Twig\Components\Admin;

use App\Repository\UserRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Admin/List.html.twig')]
class UserList implements ListComponentInterface
{
    use DefaultActionTrait;

    #[LiveProp]
    public bool $paginated = true;
    #[LiveProp]
    public string $entityName = 'user';

    public function __construct(
        private readonly UserRepository $repository,
    ) {}

    public function getEntities(): iterable
    {
        return new Pagerfanta(
            new QueryAdapter($this->repository->createQueryBuilder('u'))
        );
    }

    public function getColumns(): array
    {
        return [
            'email' => false,
            'roles' => false,
            'firstname' => true,
            'lastname' => true,
        ];
    }
}
