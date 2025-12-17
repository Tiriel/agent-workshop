<?php

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Clock\Clock;

#[AsEntityListener(event: Events::preUpdate, method: 'onPreUpdate', entity: Post::class)]
class PostUpdatedListener
{
    public function onPreUpdate(Post $post, PreUpdateEventArgs $event): void
    {
        $post->setUpdatedAt(Clock::get()->now());
    }
}
