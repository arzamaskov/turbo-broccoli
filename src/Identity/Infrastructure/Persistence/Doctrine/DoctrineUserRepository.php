<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Login;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    /**
     * @var EntityRepository<UserRecord>
     */
    private EntityRepository $records;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserMapper $userMapper,
    ) {
        $this->records = $this->entityManager->getRepository(UserRecord::class);
    }

    public function add(User $user): void
    {
        $this->entityManager->persist(
            $this->userMapper->toRecord($user),
        );
    }

    public function findById(UserId $id): ?User
    {
        return $this->toDomainOrNull(
            $this->records->find($id->value()),
        );
    }

    public function findByLogin(Login $login): ?User
    {
        return $this->toDomainOrNull(
            $this->records->findOneBy([
                'login' => $login->value(),
            ]),
        );
    }

    private function toDomainOrNull(?UserRecord $userRecord): ?User
    {
        return $userRecord === null
            ? null
            : $this->userMapper->toDomain($userRecord);
    }
}
