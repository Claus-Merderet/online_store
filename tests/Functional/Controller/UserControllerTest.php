<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DataFixtures\RoleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Enum\RoleName;
use App\Repository\UserRepository;
use App\Tests\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use AuthTrait;
    private UserRepository $userRepository;
    private KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }
    public function testRegisterSuccess(): void
    {
        $jsonData = json_encode([
            'email' => 'test@example.com',
            'password' => 'password!123',
            'phone' => '+79696969696',
            'promoId' => '',
        ]);
        $this->client->request(
            method: 'POST',
            uri: '/api/users/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $jsonData
        );
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertInstanceOf(
            User::class,
            $this->userRepository->deleteById((string)$responseData['user_id']),
            'Created user not found'
        );
        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertArrayHasKey('access_token', $responseData['tokens']);
        $this->assertArrayHasKey('refresh_token', $responseData['tokens']);
    }

    public function testAuthSuccess(): void
    {
        $response = $this->authenticateUser($this->client, UserFixtures::USER_EMAIL, UserFixtures::USER_PASSWORD);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $authResponseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $authResponseData);
        $this->assertArrayHasKey('refresh_token', $authResponseData);
    }

    public function testGetAuthenticatedUser(): void
    {
        $response = $this->authenticateUser($this->client, UserFixtures::ADMIN_PHONE, UserFixtures::ADMIN_PASSWORD);
        $authResponseData = json_decode($response->getContent(), true);
        $this->client->request(
            method: 'GET',
            uri: '/api/users/me',
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $authResponseData['token'],
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
        $this->assertContains(RoleName::ADMIN->value, $responseData['roles']);
    }

    public function testGetAuthenticatedUserInvalidToken(): void
    {
        $this->client->request(
            method: 'GET',
            uri: '/api/users/me',
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token_here',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }
}
