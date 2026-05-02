<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\UserRecord;

final class UserMapper
{
    public function toDomain(UserRecord $record): User
    {
        return new User(
            UserId::from($record->id),
            Login::from($record->login),
            PasswordHash::from($record->passwordHash),
        );
    }

    public function toRecord(User $user): UserRecord
    {
        $record = new UserRecord();
        $record->id = $user->id()->value();
        $record->login = $user->login()->value();
        $record->passwordHash = $user->passwordHash()->value();

        return $record;
    }
}
