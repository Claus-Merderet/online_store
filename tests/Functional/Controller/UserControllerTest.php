<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Enum\RoleName;
use App\Repository\UserRepository;
use App\Tests\Traits\ApiTestHelpersTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use ApiTestHelpersTrait;

    private UserRepository $userRepository;

    private KernelBrowser $client;

    private string $authAdminToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->authAdminToken = $this->authenticateAndGetToken(UserFixtures::ADMIN_PHONE, UserFixtures::ADMIN_PASSWORD);
    }

    public function testRegisterSuccess(): void
    {
        $response = $this->makeRequest(
            'POST',
            '/api/users/register',
            [
                'email' => 'test@example.com',
                'password' => 'password!123',
                'phone' => '+79696969696',
                'promoId' => '',
            ],
        );

        $responseData = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertInstanceOf(
            User::class,
            $this->userRepository->deleteById((string)$responseData['user_id']),
            'Created user not found',
        );
        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertArrayHasKey('access_token', $responseData['tokens']);
        $this->assertArrayHasKey('refresh_token', $responseData['tokens']);
    }

    public function testAuthSuccess(): void
    {
        $response = $this->makeRequest(
            'POST',
            '/api/auth/token/login',
            [
                'identifier' => UserFixtures::USER_EMAIL,
                'password' => UserFixtures::USER_PASSWORD,
            ],
        );
        $responseData = $this->assertJsonResponse($response, Response::HTTP_OK);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
    }

    public function testGetAuthenticatedUser(): void
    {
        $response = $this->makeRequest('GET', '/api/users/me', [], $this->authAdminToken);
        $responseData = $this->assertJsonResponse($response, Response::HTTP_OK);

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
        $response = $this->makeRequest('GET', '/api/users/me', [], 'invalid_token_here');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
