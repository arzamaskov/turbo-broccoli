<?php

declare(strict_types=1);

namespace App\Tests\Integration\Identity\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use App\Shared\Application\Persistence\FlusherInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineUserRepositoryTest extends KernelTestCase
{
    private UserRepositoryInterface $repository;

    private FlusherInterface $flusher;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $this->repository = $container->get(UserRepositoryInterface::class);
        $this->flusher = $container->get(FlusherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->repository, $this->flusher);
    }

    public function test_it_adds_and_finds_user_by_id(): void
    {
        $user = $this->user();

        $this->repository->add($user);
        $this->flusher->flush();

        $foundUser = $this->repository->findById($user->id());

        self::assertInstanceOf(User::class, $foundUser);
        self::assertSame($user->id()->value(), $foundUser->id()->value());
        self::assertSame($user->login()->value(), $foundUser->login()->value());
        self::assertSame($user->passwordHash()->value(), $foundUser->passwordHash()->value());
    }

    public function test_it_finds_user_by_login(): void
    {
        $user = $this->user();

        $this->repository->add($user);
        $this->flusher->flush();

        $foundUser = $this->repository->findByLogin($user->login());

        self::assertInstanceOf(User::class, $foundUser);
        self::assertSame($user->id()->value(), $foundUser->id()->value());
    }

    public function test_it_returns_null_when_user_does_not_exist(): void
    {
        self::assertNull($this->repository->findById(UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV')));
        self::assertNull($this->repository->findByLogin(Login::from('runner')));
    }

    private function user(): User
    {
        return new User(
            UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV'),
            Login::from('runner'),
            PasswordHash::from(password_hash('password', PASSWORD_ARGON2ID)),
        );
    }
}
