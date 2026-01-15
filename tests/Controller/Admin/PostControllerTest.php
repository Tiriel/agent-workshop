<?php

namespace App\Tests\Controller\Admin;

use App\Enum\PostStatus;
use App\Factory\PostFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class PostControllerTest extends WebTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testIndexDisplaysPosts(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        UserFactory::createMany(2);
        PostFactory::createMany(3);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post')
            ->assertSuccessful();
    }

    public function testShowDisplaysPostDetails(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $post = PostFactory::createOne();

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post/' . $post->getId())
            ->assertSuccessful()
            ->assertSee($post->getTitle());
    }

    public function testNewDisplaysForm(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        TagFactory::createMany(2);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post/new')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertSeeElement('input[name="post[title]"]')
            ->assertSeeElement('textarea[name="post[content]"]');
    }

    public function testCreatePost(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post/new')
            ->fillField('post[title]', 'My New Post')
            ->fillField('post[content]', 'This is the content of my post.')
            ->selectFieldOption('post[status]', PostStatus::Draft->value)
            ->interceptRedirects()
            ->click('Create Post')
            ->assertRedirectedTo('/admin/post');

        PostFactory::assert()->count(1);
        PostFactory::assert()->exists(['title' => 'My New Post']);
    }

    public function testEditDisplaysFormWithData(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $post = PostFactory::createOne();

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post/' . $post->getId() . '/edit')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertFieldEquals('post[title]', $post->getTitle());
    }

    public function testUpdatePost(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $post = PostFactory::createOne();

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/post/' . $post->getId() . '/edit')
            ->fillField('post[title]', 'Updated Title')
            ->interceptRedirects()
            ->click('Update Post')
            ->assertRedirectedTo('/admin/post');

        PostFactory::assert()->exists(['title' => 'Updated Title']);
    }

    public function testDeletePost(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $post = PostFactory::createOne();
        $postId = (string) $post->getId();

        PostFactory::assert()->count(1);

        $browser = $this->browser()->actingAs($admin)->visit('/admin/post/' . $postId);
        $token = $browser->crawler()->filter('input[name="_token"]')->attr('value');

        $browser
            ->interceptRedirects()
            ->post('/admin/post/' . $postId, [
                'body' => ['_token' => $token],
            ])
            ->assertRedirectedTo('/admin/post');

        PostFactory::assert()->count(0);
    }
}
