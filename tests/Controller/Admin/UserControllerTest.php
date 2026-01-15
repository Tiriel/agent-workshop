<?php

namespace App\Tests\Controller\Admin;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class UserControllerTest extends WebTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testIndexDisplaysUsers(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['firstname' => 'Alice', 'lastname' => 'Martin']);
        UserFactory::createOne(['firstname' => 'Bob', 'lastname' => 'Dupont']);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user')
            ->assertSuccessful()
            ->assertSee('Alice')
            ->assertSee('Bob');
    }

    public function testShowDisplaysUserDetails(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $user = UserFactory::createOne([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user/' . $user->getId())
            ->assertSuccessful()
            ->assertSee('John')
            ->assertSee('Doe')
            ->assertSee('john@example.com');
    }

    public function testNewDisplaysForm(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user/new')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertSeeElement('input[name="user[email]"]')
            ->assertSeeElement('input[name="user[firstname]"]')
            ->assertSeeElement('input[name="user[lastname]"]');
    }

    public function testCreateUser(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user/new')
            ->fillField('user[email]', 'new@example.com')
            ->fillField('user[plainPassword]', 'password123!')
            ->fillField('user[firstname]', 'Jane')
            ->fillField('user[lastname]', 'Smith')
            ->interceptRedirects()
            ->click('Create User')
            ->assertRedirectedTo('/admin/user');

        UserFactory::assert()->count(2); // Admin + New User
        UserFactory::assert()->exists(['email' => 'new@example.com']);
    }

    public function testEditDisplaysFormWithData(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $user = UserFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Wonder',
            'email' => 'alice@example.com',
        ]);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user/' . $user->getId() . '/edit')
            ->assertSuccessful()
            ->assertSeeElement('form')
            ->assertFieldEquals('user[firstname]', 'Alice')
            ->assertFieldEquals('user[lastname]', 'Wonder');
    }

    public function testUpdateUser(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $user = UserFactory::createOne([
            'firstname' => 'OldFirst',
            'lastname' => 'OldLast',
        ]);

        $this->browser()
            ->actingAs($admin)
            ->visit('/admin/user/' . $user->getId() . '/edit')
            ->fillField('user[firstname]', 'NewFirst')
            ->fillField('user[lastname]', 'NewLast')
            ->interceptRedirects()
            ->click('Update User')
            ->assertRedirectedTo('/admin/user');

        UserFactory::assert()->exists(['firstname' => 'NewFirst', 'lastname' => 'NewLast']);
    }

    public function testDeleteUser(): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $user = UserFactory::createOne(['firstname' => 'ToDelete']);
        $userId = (string) $user->getId();

        UserFactory::assert()->count(2); // Admin + User

        $browser = $this->browser()->actingAs($admin)->visit('/admin/user/' . $userId);
        $token = $browser->crawler()->filter('input[name="_token"]')->attr('value');

        $browser
            ->interceptRedirects()
            ->post('/admin/user/' . $userId, [
                'body' => ['_token' => $token],
            ])
            ->assertRedirectedTo('/admin/user');

        UserFactory::assert()->count(1); // Only Admin left
    }
}
