<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Entity;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_it_creates_user(): void
    {
        $id = UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV');
        $login = Login::from('runner');
        $passwordHash = PasswordHash::from($this->passwordHash());

        $user = new User($id, $login, $passwordHash);

        self::assertTrue($id->equals($user->id()));
        self::assertTrue($login->equals($user->login()));
        self::assertTrue($passwordHash->equals($user->passwordHash()));
    }

    public function test_it_changes_login(): void
    {
        $user = $this->user();
        $newLogin = Login::from('trail-runner');

        $user->changeLogin($newLogin);

        self::assertTrue($newLogin->equals($user->login()));
    }

    public function test_it_changes_password(): void
    {
        $user = $this->user();
        $newPasswordHash = PasswordHash::from($this->passwordHash('new-password'));

        $user->changePassword($newPasswordHash);

        self::assertTrue($newPasswordHash->equals($user->passwordHash()));
    }

    private function user(): User
    {
        return new User(
            UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV'),
            Login::from('runner'),
            PasswordHash::from($this->passwordHash()),
        );
    }

    private function passwordHash(string $password = 'password'): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
}
