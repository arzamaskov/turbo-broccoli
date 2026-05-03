<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Application\Persistence\FlusherInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFlusher implements FlusherInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
