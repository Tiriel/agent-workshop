<?php

namespace App\Ai\Tool;

use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsTool(
    name: 'auto_tag_posts',
    description: 'Add tags to posts lacking tags using semantic similarity.'
)]
class SimilarityPostTagger
{
    public function __construct(
        #[Target('ai.retriever.tags')]
        private readonly RetrieverInterface $retriever,
        #[Autowire(param: 'app.similarity_threshold')]
        private readonly float $threshold,
        private readonly PostRepository $postRepository,
        private readonly TagRepository $tagRepository,
    ) {}

    public function __invoke(): string
    {
        $posts = $this->postRepository->findWithoutTags();

        foreach ($posts as $post) {
            foreach ($this->retriever->retrieve($post->getContent()) as $doc) {
                if ($doc->score >= $this->threshold) {
                    $post->addTag($this->tagRepository->find($doc->id));
                }

                $this->postRepository->save($post);
            }
        }

        return sprintf("Tagged %d posts", count($posts));
    }
}
