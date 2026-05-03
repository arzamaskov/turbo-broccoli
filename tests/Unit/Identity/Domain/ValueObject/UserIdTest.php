<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject;

use App\Identity\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class UserIdTest extends TestCase
{
    public function test_it_creates_user_id_from_valid_ulid(): void
    {
        $ulid = (new Ulid())->toBase32();
        $userId = UserId::from($ulid);
        $this->assertSame($userId->value(), $ulid);
    }

    public function test_it_generates_user_id(): void
    {
        $userId = UserId::generate();
        $anotherUserId = UserId::generate();

        $this->assertMatchesRegularExpression('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', $userId->value());
        $this->assertFalse($userId->equals($anotherUserId));
    }

    public function test_it_throws_exception_for_invalid_ulid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user id');

        UserId::from('not-a-uuid');
    }

    public function test_it_compares_user_ids_by_value(): void
    {
        $ulid1 = (new Ulid())->toBase32();
        $ulid2 = (new Ulid())->toBase32();

        $userId1 = UserId::from($ulid1);
        $userId1_copy = UserId::from($ulid1);
        $userId2 = UserId::from($ulid2);

        $this->assertTrue($userId1->equals($userId1_copy));
        $this->assertFalse($userId1->equals($userId2));
    }
}
