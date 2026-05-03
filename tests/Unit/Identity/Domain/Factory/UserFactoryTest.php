<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Factory;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Factory\UserFactory;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserFactoryTest extends TestCase
{
    public function test_it_creates_user_with_generated_id(): void
    {
        $factory = new UserFactory(new InMemoryUserRepository());
        $passwordHash = $this->passwordHash();

        $user = $factory->create('Runner', $passwordHash);

        self::assertMatchesRegularExpression('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', $user->id()->value());
        self::assertSame('runner', $user->login()->value());
        self::assertSame($passwordHash, $user->passwordHash()->value());
    }

    public function test_it_rejects_existing_login(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->add(new User(
            UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV'),
            Login::from('runner'),
            PasswordHash::from($this->passwordHash()),
        ));

        $factory = new UserFactory($repository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Login already exists.');

        $factory->create('Runner', $this->passwordHash('new-password'));
    }

    public function test_it_rejects_invalid_password_hash(): void
    {
        $factory = new UserFactory(new InMemoryUserRepository());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password hash');

        $factory->create('runner', 'plain-password');
    }

    private function passwordHash(string $password = 'password'): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
}

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $usersById = [];

    /** @var array<string, User> */
    private array $usersByLogin = [];

    public function add(User $user): void
    {
        $this->usersById[$user->id()->value()] = $user;
        $this->usersByLogin[$user->login()->value()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->usersById[$id->value()] ?? null;
    }

    public function findByLogin(Login $login): ?User
    {
        return $this->usersByLogin[$login->value()] ?? null;
    }
}
