<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private UserRepository $userRepository;
    private KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
    }
    public function testRegisterSuccess(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password!123',
            'phone' => '+79696969696',
            'promoId' => '',
        ];
        $this->client->request(
            'POST',
            '/api/users/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertArrayHasKey('access_token', $responseData['tokens']);
        $this->assertArrayHasKey('refresh_token', $responseData['tokens']);
        $this->assertInstanceOf(User::class, $this->userRepository->deleteById((string)$responseData['user_id']));
    }

    public function testAuthSuccess(): void
    {
        $accessToken = $this->authenticateUser('justuser@example.com', 'test123!');
        $this->assertNotEmpty($accessToken);
    }

    public function testGetAuthenticatedUser(): void
    {
        $accessToken = $this->authenticateUser('+79493223333', 'admin123!');
        $this->client->request(
            'GET',
            '/api/users/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken,
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('roles', $responseData);

        $this->assertEquals('admin@example.com', $responseData['email']);
        $this->assertEquals('+79493223333', $responseData['phone']);
        $this->assertContains('ADMIN_ROLE', $responseData['roles']);
    }

    public function testGetAuthenticatedUserInvalidToken(): void
    {
        $this->client->request(
            'GET',
            '/api/users/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token_here',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }
    private function authenticateUser(string $identifier, string $password): string
    {
        $authData = [
            'identifier' => $identifier,
            'password' => $password,
        ];
        $this->client->request(
            'POST',
            '/api/auth/token/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($authData)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $authResponseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $authResponseData);
        $this->assertArrayHasKey('refresh_token', $authResponseData);

        return $authResponseData['token'];
    }
}
