<?php

declare(strict_types=1);

namespace App\Identity\Domain\Factory;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use InvalidArgumentException;

final readonly class UserFactory
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function create(string $login, string $passwordHash): User
    {
        if ($this->userRepository->findByLogin(Login::from($login))) {
            throw new InvalidArgumentException('Login already exists.');
        }

        $id = UserId::generate();

        return new User(
            id: $id,
            login: Login::from($login),
            passwordHash: PasswordHash::from($passwordHash),
        );
    }
}
