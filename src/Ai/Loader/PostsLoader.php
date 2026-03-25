<?php

namespace App\Ai\Loader;

use App\Repository\PostRepository;
use Symfony\AI\Store\Document\LoaderInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PostsLoader implements LoaderInterface
{
    public function __construct(
        private readonly PostRepository $repository,
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function load(?string $source = null, array $options = []): iterable
    {
        foreach ($this->repository->findAll() as $post) {
            yield new TextDocument(
                id: $post->getId(),
                content: sprintf(
                    "Title: %s\nAuthor: %s %s\nContent:\n%s",
                    $post->getTitle(),
                    $post->getAuthor()->getFirstName(),
                    $post->getAuthor()->getLastName(),
                    $post->getContent(),
                ),
                metadata: new Metadata($this->normalizer->normalize($post, context: [
                    AbstractNormalizer::ATTRIBUTES => [
                        'id',
                        'title',
                        'status',
                        'tags' => ['name'],
                        'author' => ['id', 'firstname', 'lastname'],
                    ],
                ])),
            );
        }
    }
}
