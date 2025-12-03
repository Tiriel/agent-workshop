<?php

namespace App\Story;

use App\Factory\PostFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function __construct(
        #[Autowire(env: 'ADMIN_EMAIL')]
        private readonly string $adminEmail,
        #[Autowire(env: 'ADMIN_PWD')]
        private readonly string $adminPwd,
    )
    {
    }

    public function build(): void
    {
        UserFactory::new()
            ->email($this->adminEmail)
            ->password($this->adminPwd)
            ->roles(['ROLE_ADMIN'])
            ->firstname('Joe')
            ->lastname('Admin')
            ->create();

        UserFactory::createMany(5);
        TagFactory::createMany(20);
        PostFactory::createMany(50);
    }
}
