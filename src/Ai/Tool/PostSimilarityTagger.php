<?php

namespace App\Ai\Tool;

use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTool(name: 'auto_tag_posts', description: 'Tags posts using semantic similarity')]
final class PostSimilarityTagger
{
    // Hybrid (vector + full-text) retriever scores: higher is better, typically 0.2 to 0.3
    private const SIMILARITY_THRESHOLD = 0.25;

    public function __construct(
        #[Autowire('@ai.retriever.tags')] private readonly RetrieverInterface $retriever,
        private readonly PostRepository $postRepo,
        private readonly TagRepository $tagRepo,
    ) {}

    public function __invoke(?int $limit = null): string
    {
        $posts = $this->postRepo->findWithoutTags($limit);
        foreach ($posts as $post) {
            foreach ($this->retriever->retrieve($post->getContent()) as $doc) {
                if ($doc->getScore() >= self::SIMILARITY_THRESHOLD) {
                    $post->addTag($this->tagRepo->find($doc->getId()));
                }
            }
            $this->postRepo->save($post);
        }
        return sprintf('Tagged %d posts', count($posts));
    }
}
