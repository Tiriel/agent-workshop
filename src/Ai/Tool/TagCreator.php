<?php

namespace App\Ai\Tool;

use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Bridge\Anthropic\Claude;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTool(
    name: 'generate_tags_for_posts',
    description: 'Generates new tags using AI for posts that have no tags or not enough similar tags.',
)]
final class TagCreator
{
    private const int MAX_TAGS_PER_POST = 5;

    public function __construct(
        #[Autowire(service: 'ai.platform.anthropic')]
        private readonly PlatformInterface $platform,
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
            $result = $this->generateTagsForPost($post);
            if (null !== $result) {
                $results[] = $result;
            }
        }

        return sprintf("Generated tags for %d post(s):\n%s", count($results), implode("\n", $results));
    }

    private function generateTagsForPost(Post $post): ?string
    {
        $content = $post->getContent();
        if (null === $content || '' === trim($content)) {
            return null;
        }

        $prompt = sprintf(
            "Analyse ce contenu et suggère exactement %d tags pertinents. Réponds UNIQUEMENT avec les tags séparés par des virgules, sans explication.\n\nContenu:\n%s",
            self::MAX_TAGS_PER_POST,
            $content
        );

        $response = $this->platform->invoke(Claude::HAIKU_3, new MessageBag(Message::ofUser($prompt)));
        $tagNamesRaw = (string) $response->getResult()->getContent();

        $tagNames = array_filter(array_map('trim', explode(',', $tagNamesRaw)));
        if (count($tagNames) === 0) {
            return null;
        }

        $createdTags = [];

        foreach ($tagNames as $name) {
            $tag = $this->findOrCreateTag($name);
            $post->addTag($tag);
            $createdTags[] = $name;
        }

        $this->postRepository->save($post);

        return sprintf('- "%s": %s', $post->getTitle(), implode(', ', $createdTags));
    }

    private function findOrCreateTag(string $name): ?Tag
    {
        $tag = $this->tagRepository->findOneByName($name)
                ?? (new Tag())->setName($name);

        if (null === $tag->getId()) {
            $this->tagRepository->save($tag);
        }

        return $tag;
    }
}

