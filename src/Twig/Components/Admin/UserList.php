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

    #[LiveProp]
    public int $currentPage = 1;

    public function __construct(
        private readonly UserRepository $repository,
        private readonly int $maxPerPage,
    ) {}

    public function getEntities(): iterable
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->repository->createQueryBuilder('u')),
            $this->currentPage,
            $this->maxPerPage,
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
