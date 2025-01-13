<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        $data = [
            'email' => 'test@example.com',
            'password' => 'password!123',
            'phone' => '+79696969696',
            'promoId' => '',
        ];
        $client->request(
            'POST',
            '/api/users/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }
}
