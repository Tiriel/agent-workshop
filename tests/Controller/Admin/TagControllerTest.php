<?php

namespace App\Tests\Controller\Admin;

use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class TagControllerTest extends WebTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testIndexDisplaysTags(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        TagFactory::createOne(['name' => 'Symfony']);
        TagFactory::createOne(['name' => 'PHP']);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag')
            ->assertSuccessful()
            ->assertSee('Symfony')
            ->assertSee('PHP');
    }

    public function testShowDisplaysTagDetails(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $tag = TagFactory::createOne(['name' => 'Docker']);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag/' . $tag->getId())
            ->assertSuccessful()
            ->assertSee('Docker');
    }

    public function testNewDisplaysForm(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag/new')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertSeeElement('input[name="tag[name]"]');
    }

    public function testCreateTag(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag/new')
            ->fillField('tag[name]', 'Kubernetes')
            ->interceptRedirects()
            ->click('Create Tag')
            ->assertRedirectedTo('/admin/tag');

        TagFactory::assert()->count(1);
        TagFactory::assert()->exists(['name' => 'Kubernetes']);
    }

    public function testEditDisplaysFormWithData(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $tag = TagFactory::createOne(['name' => 'OldName']);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag/' . $tag->getId() . '/edit')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertFieldEquals('tag[name]', 'OldName');
    }

    public function testUpdateTagDoesntWork(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $tag = TagFactory::createOne(['name' => 'OldName']);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/tag/' . $tag->getId() . '/edit')
            ->fillField('tag[name]', 'NewName')
            ->interceptRedirects()
            ->click('Update Tag')
            ->assertRedirectedTo('/admin/tag');

        TagFactory::assert()->notExists(['name' => 'NewName']);
        TagFactory::assert()->exists(['name' => 'OldName']);
    }

    public function testDeleteTag(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $tag = TagFactory::createOne(['name' => 'ToDelete']);
        $tagId = (string) $tag->getId();

        TagFactory::assert()->count(1);

        $browser = $this->browser()->actingAs($admin)->visit('/admin/tag/' . $tagId);
        $token = $browser->crawler()->filter('input[name="_token"]')->attr('value');

        $browser
            ->interceptRedirects()
            ->post('/admin/tag/' . $tagId, [
                'body' => ['_token' => $token],
            ])
            ->assertRedirectedTo('/admin/tag');

        TagFactory::assert()->count(0);
    }
}
