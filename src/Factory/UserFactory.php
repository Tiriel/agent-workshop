<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    public function email(string $email): static
    {
        return $this->with(['email' => $email]);
    }

    public function password(string $password): static
    {
        return $this->with(['password' => $password]);
    }

    public function roles(array $roles): static
    {
        return $this->with(['roles' => $roles]);
    }

    public function firstname(string $firstname): static
    {
        return $this->with(['firstname' => $firstname]);
    }

    public function lastname(string $lastname): static
    {
        return $this->with(['lastname' => $lastname]);
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
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
            'email' => self::faker()->email(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'password' => 'abcd1234!',
            'roles' => ['ROLE_USER'],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (User $entity): void {
                $entity->setPassword($this->hasher->hashPassword($entity, $entity->getPassword()));
            })
        ;
    }
}
