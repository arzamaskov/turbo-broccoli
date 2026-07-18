<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Bus;

use App\Shared\Application\Command\CommandInterface;
use App\Shared\Infrastructure\Bus\CommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class CommandBusTest extends TestCase
{
    public function test_it_executes_command_with_single_handler(): void
    {
        $command = new TestCommand('payload');
        $handler = new TestCommandHandler();
        $bus = new CommandBus(new MessageBus([
            new HandleMessageMiddleware(
                new HandlersLocator([
                    TestCommand::class => [$handler],
                ]),
            ),
        ]));
    }
}

final readonly class TestCommand implements CommandInterface
{
    public function __construct(public string $payload) {}
}

final class TestCommandHandler
{
    public ?TestCommand $handledCommand = null;

    public function __invoke(TestCommand $command): string
    {
        $this->handledCommand = $command;

        return 'handled:' . $command->payload;
    }
}
