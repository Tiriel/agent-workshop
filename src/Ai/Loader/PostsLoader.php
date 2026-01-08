<?php

namespace App\Ai\Loader;

use Symfony\AI\Store\Document\LoaderInterface;

class PostsLoader extends AbstractPostLoader implements LoaderInterface
{
    public function load(?string $source = null, array $options = []): iterable
    {
        $documents = [];
        $posts = $this->repository->findAll();
        foreach ($posts as $post) {
            $documents[] = $this->getTextDocumentFromPost($post);
        }

        return $documents;
    }
}
