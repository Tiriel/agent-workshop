<?php

namespace App\Twig\Components\Admin;

use App\Repository\PostRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Admin/List.html.twig')]
final class PostList implements ListComponentInterface
{
    use DefaultActionTrait;

    #[LiveProp]
    public bool $paginated = true;

    #[LiveProp]
    public string $entityName = 'post';

    public function __construct(
        private readonly PostRepository $repository,
    ) {}

    public function getEntities(): iterable
    {
        return new Pagerfanta(
            new QueryAdapter($this->repository->createQueryBuilder('p'))
        );
    }

    public function getColumns(): array
    {
        return [
            'title' => false,
            'author' => false,
            'status' => false,
            'createdAt' => false,
            'updatedAt' => true,
            'publishedAt' => true,
        ];
    }
}
