<?php

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\Clock;

#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: Post::class)]
class PostCreatedListener
{
    public function __construct(
        private readonly Security $security,
    ) {}

    public function onPrePersist(Post $post, PrePersistEventArgs $event): void
    {
        $post
            ->setCreatedAt(Clock::get()->now())
            ->setAuthor($this->security->getUser() ?? $post->getAuthor())
        ;
    }
}
