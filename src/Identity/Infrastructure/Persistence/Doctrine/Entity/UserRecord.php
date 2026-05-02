<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('identity_users')]
#[ORM\UniqueConstraint(name: 'unique_identity_users_login', columns: ['login'])]
final class UserRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 26, unique: true)]
    public string $id;

    #[ORM\Column(type: 'string', length: 32)]
    public string $login;

    #[ORM\Column(name: 'password_hash', type: 'string', length: 255)]
    public string $passwordHash;
}
