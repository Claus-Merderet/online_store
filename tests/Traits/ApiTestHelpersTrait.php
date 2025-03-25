<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ApiTestHelpersTrait
{
    use AuthTrait;

    private KernelBrowser $client;

    private ?string $authToken = null;

    /**
     * Отправляет API-запрос с авторизацией
     *
     * @param string $method HTTP-метод (GET, POST, PUT, DELETE и т.д.)
     * @param string $uri URI endpoint
     * @param array<string, mixed> $data Данные для отправки (будут преобразованы в JSON)
     * @param string|null $token Токен авторизации (если null, используется установленный ранее)
     */
    protected function makeRequest(
        string $method,
        string $uri,
        array $data = [],
        ?string $token = null,
    ): Response {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . ($token ?? $this->authToken),
            ],
            json_encode($data),
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<string, mixed>
     */
    protected function assertJsonResponse(
        Response $response,
        int $expectedStatusCode,
    ): array {
        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw new RuntimeException(
                sprintf(
                    'Expected status %d, got %d. Response: %s',
                    $expectedStatusCode,
                    $response->getStatusCode(),
                    $response->getContent(),
                ),
            );
        }

        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    protected function authenticateAndGetToken(string $email, string $password): string
    {
        $response = $this->authenticateUser($this->client, $email, $password);

        return json_decode($response->getContent(), true)['token'];
    }
}
