<?php

namespace App\Ai\Loader;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\AI\Store\Document\LoaderInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;

class TagsLoader implements LoaderInterface
{
    public function __construct(
        private readonly TagRepository $repository,
    ) {
    }

    public function load(?string $source = null, array $options = []): iterable
    {
        /** @var Tag[] $tags */
        $tags = $this->repository->findAll();

        foreach ($tags as $tag) {
            $name = $tag->getName();
            if (null === $name || '' === trim($name)) {
                continue;
            }

            yield new TextDocument(
                id: $tag->getId(),
                content: $name,
            );
        }
    }
}
