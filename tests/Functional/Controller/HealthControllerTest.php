<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HealthControllerTest extends WebTestCase
{
    public function test_request_responded_successfully(): void
    {
        $client = self::createClient();
        $router = self::getContainer()->get('router');
        $client->request(Request::METHOD_GET, $router->generate('health'));

        self::assertResponseIsSuccessful();
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('OK', $jsonResponse['status']);
    }
}
