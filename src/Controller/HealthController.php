<?php

declare(strict_types=1);

namespace App\Controller;

use PDO;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final readonly class HealthController
{
    public function __construct(
        private string $projectDir,
    ) {}

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        try {
            $this->checkSqlite();
        } catch (Throwable $exception) {
            return new JsonResponse([
                'status' => 'error',
                'error' => $exception->getMessage(),
            ], 503);
        }

        return new JsonResponse(['status' => 'OK']);
    }

    private function checkSqlite(): void
    {
        $databaseUrl = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: '';
        $projectDirPrefix = 'sqlite:///%kernel.project_dir%/';
        $appDirPrefix = 'sqlite:////app/';

        if ($databaseUrl === '') {
            return;
        }

        $path = match (true) {
            str_starts_with($databaseUrl, $projectDirPrefix) => $this->projectDir . '/' . substr($databaseUrl, strlen($projectDirPrefix)),
            str_starts_with($databaseUrl, $appDirPrefix) => '/app/' . substr($databaseUrl, strlen($appDirPrefix)),
            default => null,
        };

        if ($path === null) {
            return;
        }

        $directory = dirname($path);

        if (!is_dir($directory) || !is_writable($directory)) {
            throw new RuntimeException('sqlite directory is not writable');
        }

        if (!is_file($path) || !is_writable($path)) {
            throw new RuntimeException('sqlite database is not writable');
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->query('PRAGMA quick_check');

        if ($statement === false) {
            throw new RuntimeException('sqlite quick_check failed');
        }

        $result = $statement->fetchColumn();

        if ($result !== 'ok') {
            throw new RuntimeException('sqlite quick_check failed');
        }
    }
}
