<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Tests\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    use AuthTrait;
    private ProductRepository $productRepository;
    private KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->productRepository = static::getContainer()->get(ProductRepository::class);
    }

    public function testGetProductListSuccess(): void
    {
        $this->client->request('GET', '/api/products');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData['data'], 'Received an empty product list');
    }

    public function testCreateProductSuccess(): void
    {
        $response = $this->authenticateUser($this->client, UserFixtures::ADMIN_EMAIL, UserFixtures::ADMIN_PASSWORD);
        $responseData = json_decode($response->getContent(), true);
        $jsonData = json_encode($this->createTestProductData());
        $this->client->request(
            method: 'POST',
            uri: '/api/products',
            server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $responseData['token'],
        ],
            content: $jsonData
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertInstanceOf(
            Product::class,
            $this->productRepository->deleteById((string)$responseData['id']),
            'Created product not found'
        );
    }

    public function testUpdateProductSuccess(): void
    {
        $response = $this->authenticateUser($this->client, UserFixtures::ADMIN_EMAIL, UserFixtures::ADMIN_PASSWORD);
        $responseData = json_decode($response->getContent(), true);
        $productArray = $this->productRepository->findByIdAsArray('1');
        $jsonData = json_encode([
            'id' => $productArray['id'],
            'name' => $productArray['name'],
            'measurements' => [
                'weight' => $productArray['weight'],
                'height' => $productArray['height'],
                'width' => $productArray['width'],
                'length' => $productArray['length'],
            ],
            'description' => $productArray['description'],
            'cost' => $productArray['price'],
            'tax' => $productArray['tax'],
            'version' => $productArray['version'] + 1,
        ]);
        $this->client->request(
            method: 'PUT',
            uri: '/api/products',
            server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $responseData['token'],
        ],
            content: $jsonData
        );

        $this->assertEquals(Response::HTTP_ACCEPTED, $this->client->getResponse()->getStatusCode());
        $product = $this->productRepository->findById('1');
        $this->assertEquals(
            $productArray['version'] + 1,
            $product->getVersion(),
            'The version field is not updated'
        );
    }

    /**
     * @return array{id: int, name: string, measurements: array{weight: int, height: int, width: int, length: int}, description: string, cost: int, tax: int, version: int}
     */
    private function createTestProductData(): array
    {
        return [
            'id' => 100,
            'name' => 'Test Product',
            'measurements' => [
                'weight' => 1,
                'height' => 1,
                'width' => 1,
                'length' => 1,
            ],
            'description' => 'Test Description',
            'cost' => 100,
            'tax' => 10,
            'version' => 1,
        ];
    }
}
