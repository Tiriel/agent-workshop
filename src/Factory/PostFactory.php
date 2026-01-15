<?php

namespace App\Factory;

use App\Entity\Post;
use App\Enum\PostStatus;
use Symfony\Component\Finder\Finder;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
{
    private array $fixtures = [];
    private array $current = [];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        $finder = new Finder()->in(__DIR__.'/fixtures')->files()->name('*_fr.json');
        $jsonFixtures = \file_get_contents(key(\iterator_to_array($finder)));
        $this->fixtures = \json_decode($jsonFixtures, true);
    }

    #[\Override]
    public static function class(): string
    {
        return Post::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'author' => UserFactory::random(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween(startDate: '01-01-2022', endDate: 'now')),
            'status' => self::faker()->randomElement(PostStatus::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(Post $post) {
                $this->current = self::faker()->randomElement($this->fixtures);
                unset($this->fixtures[\array_search($this->current, $this->fixtures, true)]);
                $post
                    ->setTitle($this->current['title'])
                    ->setContent($this->current['content'])
                ;

                if (PostStatus::Published === $post->getStatus()) {
                    $post->setPublishedAt(
                        \DateTimeImmutable::createFromMutable(
                            self::faker()->dateTimeBetween($post->getCreatedAt()->format('Y-m-d'), 'now')
                        )
                    );
                }
                foreach ($this->current['tags'] as $tag) {
                    $post->addTag(TagFactory::findOrCreate(['name' => $tag]));
                }
            })
        ;
    }
}
