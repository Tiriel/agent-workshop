<?php

namespace App\Ai\Loader;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\AI\Store\Document\LoaderInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractPostLoader implements LoaderInterface
{
    public function __construct(
        protected readonly PostRepository $repository,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    protected function getTextDocumentFromPost(Post $post): TextDocument
    {
        return new TextDocument(
            id: $post->getId(),
            content: sprintf("Title: %s\nAuthor: %s %s\nContent:\n%s",
                $post->getTitle(),
                $post->getAuthor()->getFirstname(),
                $post->getAuthor()->getLastname(),
                $post->getContent()
            ),
            metadata: new Metadata($this->normalizer->normalize($post, context: [
                AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'content', 'createdAt', 'updatedAt', 'publishedAt', 'status', 'tags' => ['name'], 'author' => ['id', 'email', 'firstname', 'lastname']],
            ]))
        );
    }
}
