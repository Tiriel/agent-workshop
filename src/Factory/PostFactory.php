<?php

namespace App\Factory;

use App\Entity\Post;
use App\Enum\PostStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
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
            'author' => UserFactory::randomOrCreate(),
            'content' => self::faker()->realText(self::faker()->numberBetween(200, 800)),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime(max: 'now')),
            'status' => self::faker()->randomElement(PostStatus::cases()),
            'title' => self::faker()->text(255),
            'tags' => TagFactory::randomRangeOrCreate(0, 5),
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
                if (PostStatus::PUBLISHED === $post->getStatus()) {
                    $post->setPublishedAt(
                        \DateTimeImmutable::createFromMutable(
                            self::faker()->dateTimeBetween($post->getCreatedAt()->format('Y-m-d'), 'now')
                        )
                    );
                }
            })
        ;
    }
}
