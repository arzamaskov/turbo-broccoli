<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Login extends StringValueObject
{
    public const int MIN_LENGTH = 3;
    public const int MAX_LENGTH = 32;
    private const string PATTERN = '/^[a-zA-Z0-9._-]+$/';

    public static function from(string $login): self
    {
        $normalized = strtolower(trim($login));
        if ($normalized === '') {
            throw new InvalidArgumentException('Login cannot be empty');
        }

        if (strlen($normalized) < self::MIN_LENGTH) {
            throw new InvalidArgumentException('Login cannot be shorter than ' . self::MIN_LENGTH . ' characters');
        }

        if (strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Login cannot be longer than ' . self::MAX_LENGTH . ' characters');
        }

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            throw new InvalidArgumentException('Login ' . $login . ' contains invalid characters');
        }

        return new self($normalized);
    }
}
