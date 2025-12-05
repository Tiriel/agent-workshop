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

    public function __construct(
        private readonly TagRepository $repository,
    ) {}

    public function getEntities(): iterable
    {
        return new Pagerfanta(
            new QueryAdapter($this->repository->createQueryBuilder('t'))
        );
    }

    public function getColumns(): array
    {
        return [
            'name' => false,
        ];
    }
}
