<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\PasswordHash;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use App\Identity\Infrastructure\Persistence\Doctrine\UserMapper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserMapperTest extends TestCase
{
    public function test_it_maps_record_to_domain_user(): void
    {
        $record = $this->record();

        $user = (new UserMapper())->toDomain($record);

        self::assertSame($record->id, $user->id()->value());
        self::assertSame($record->login, $user->login()->value());
        self::assertSame($record->passwordHash, $user->passwordHash()->value());
    }

    public function test_it_maps_domain_user_to_record(): void
    {
        $user = new User(
            UserId::from('01ARZ3NDEKTSV4RRFFQ69G5FAV'),
            Login::from('runner'),
            PasswordHash::from($this->passwordHash()),
        );

        $record = (new UserMapper())->toRecord($user);

        self::assertSame($user->id()->value(), $record->id);
        self::assertSame($user->login()->value(), $record->login);
        self::assertSame($user->passwordHash()->value(), $record->passwordHash);
    }

    public function test_it_rejects_record_with_invalid_user_id(): void
    {
        $record = $this->record();
        $record->id = 'not-a-ulid';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user id');

        (new UserMapper())->toDomain($record);
    }

    public function test_it_rejects_record_with_invalid_login(): void
    {
        $record = $this->record();
        $record->login = 'bad login';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contains invalid characters');

        (new UserMapper())->toDomain($record);
    }

    public function test_it_rejects_record_with_invalid_password_hash(): void
    {
        $record = $this->record();
        $record->passwordHash = 'not-a-password-hash';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password hash');

        (new UserMapper())->toDomain($record);
    }

    private function record(): UserRecord
    {
        $record = new UserRecord();
        $record->id = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
        $record->login = 'runner';
        $record->passwordHash = $this->passwordHash();

        return $record;
    }

    private function passwordHash(string $password = 'password'): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
}
