<?php

namespace App\Ai\Loader;

use App\Repository\TagRepository;
use Symfony\AI\Store\Document\LoaderInterface;
use Symfony\AI\Store\Document\TextDocument;

class TagsLoader implements LoaderInterface
{
    public function __construct(
        private readonly TagRepository $repository,
    ) {}

    public function load(?string $source = null, array $options = []): iterable
    {
        foreach ($this->repository->findAll() as $tag) {
            if (null === $tag->getName() || '' === trim($tag->getName())) {
                continue;
            }

            yield new TextDocument(
                id: $tag->getId(),
                content: $tag->getName(),
            );
        }
    }
}
