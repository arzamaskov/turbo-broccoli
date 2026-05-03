<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

final readonly class UserId extends StringValueObject
{
    private const string ULID_PATTERN = '/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/';

    public static function from(string $ulid): self
    {
        if (preg_match(self::ULID_PATTERN, $ulid) !== 1) {
            throw new InvalidArgumentException('Invalid user id');
        }

        return new self($ulid);
    }

    public static function generate(): self
    {
        return new self(Ulid::generate());
    }
}
