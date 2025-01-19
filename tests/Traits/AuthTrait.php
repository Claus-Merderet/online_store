<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait AuthTrait
{
    /**
     * Авторизует пользователя и возвращает Response c массивом [token => '', refresh_token => ''].
     */
    protected function authenticateUser(KernelBrowser $client, string $identifier, string $password): Response
    {
        $client->request(
            method: 'POST',
            uri: '/api/auth/token/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'identifier' => $identifier,
                'password' => $password,
            ])
        );

        return $client->getResponse();
    }
}
