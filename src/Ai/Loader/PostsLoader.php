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
            $content = $post->getTitle().\PHP_EOL.$post->getContent();
            if (null === $content || '' === trim($content)) {
                continue;
            }

            yield new TextDocument(
                id: $post->getId(),
                content: sprintf("Title: %s\nAuthor: %s %s\nContent: %s",
                    $post->getTitle(),
                    $post->getAuthor()->getFirstname(),
                    $post->getAuthor()->getLastname(),
                    $post->getContent()
                ),
                metadata: new Metadata($this->normalizer->normalize($post, context: [
                    AbstractNormalizer::ATTRIBUTES => [
                        'id',
                        'title',
                        'status',
                        'tags' => ['name'],
                        'author' => ['id', 'firstname', 'lastname']
                    ]
                ]))
            );
        }
    }
}
