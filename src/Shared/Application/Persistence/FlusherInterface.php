<?php

declare(strict_types=1);

namespace App\Shared\Application\Persistence;

interface FlusherInterface
{
    public function flush(): void;
}
