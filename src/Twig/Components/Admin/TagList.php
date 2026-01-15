<?php

namespace App\Twig\Components\Admin;

use App\Repository\TagRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Admin/List.html.twig')]
class TagList implements ListComponentInterface
{
    use DefaultActionTrait;

    #[LiveProp]
    public bool $paginated = true;

    #[LiveProp]
    public string $entityName = 'tag';

    #[LiveProp]
    public int $currentPage = 1;

    public function __construct(
        private readonly TagRepository $repository,
        private readonly int $maxPerPage = 10,
    ) {
    }

    public function getEntities(): iterable
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($this->repository->createQueryBuilder('t')),
            $this->currentPage,
            $this->maxPerPage,
        );
    }

    public function getColumns(): array
    {
        return [
            'name' => false,
        ];
    }
}
