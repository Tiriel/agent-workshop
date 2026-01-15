<?php

namespace App\Ai\Tool;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTool(
    name: 'auto_tag_posts',
    description: 'Finds posts without tags and adds relevant tags based on semantic similarity.',
)]
final class SimilarityPostTagger
{
    private const float MIN_SIMILARITY_SCORE = 0.5;
    private const int MAX_TAGS_PER_POST = 5;

    public function __construct(
        #[Autowire('@ai.retriever.tags')]
        private readonly RetrieverInterface $tagsRetriever,
        private readonly PostRepository $postRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function __invoke(): string
    {
        $postsWithoutTags = $this->postRepository->findWithoutTags();

        if (count($postsWithoutTags) === 0) {
            return 'No posts without tags found.';
        }

        $results = [];

        foreach ($postsWithoutTags as $post) {
            $result = $this->tagPost($post);
            if (null !== $result) {
                $results[] = $result;
            }
        }

        if (count($results) === 0) {
            return sprintf('Found %d post(s) without tags, but no relevant tags were found.', count($postsWithoutTags));
        }

        return sprintf("Tagged %d post(s):\n%s", count($results), implode("\n", $results));
    }

    private function tagPost(Post $post): ?string
    {
        $content = $post->getContent();
        $tagDocuments = $this->tagsRetriever->retrieve($content);

        $addedTags = [];
        $count = 0;

        foreach ($tagDocuments as $document) {
            if ($count >= self::MAX_TAGS_PER_POST) {
                break;
            }

            if (null !== $document->score && $document->score < self::MIN_SIMILARITY_SCORE) {
                continue;
            }

            $tag = $this->tagRepository->find($document->id);

            $post->addTag($tag);
            $addedTags[] = $tag->getName();
            ++$count;
        }

        if (count($addedTags) === 0) {
            return null;
        }

        $this->postRepository->save($post);

        return sprintf('- "%s": %s', $post->getTitle(), implode(', ', $addedTags));
    }
}
