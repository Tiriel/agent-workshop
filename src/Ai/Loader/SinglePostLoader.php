<?php

namespace App\Ai\Loader;

use Symfony\AI\Store\Document\LoaderInterface;

class SinglePostLoader extends AbstractPostLoader implements LoaderInterface
{
    public function load(?string $source = null, array $options = []): iterable
    {
        return [$this->getTextDocumentFromPost($this->repository->find($source))];
    }
}
