<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Tests\Traits\ApiTestHelpersTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    use ApiTestHelpersTrait;

    private ProductRepository $productRepository;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->authToken = $this->authenticateAndGetToken(UserFixtures::ADMIN_EMAIL, UserFixtures::ADMIN_PASSWORD);
    }

    public function testGetProductListSuccess(): void
    {
        $response = $this->makeRequest('GET', '/api/products');
        $responseData = $this->assertJsonResponse($response, Response::HTTP_OK);
        $this->assertNotEmpty($responseData['data'], 'Received an empty product list');
    }

    public function testCreateProductSuccess(): void
    {
        $response = $this->makeRequest('POST', '/api/products', $this->createTestProductData());
        $responseData = $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertInstanceOf(
            Product::class,
            $this->productRepository->deleteById($responseData['id']),
            'Created product not found',
        );
    }

    public function testUpdateProductSuccess(): void
    {
        $product = $this->productRepository->find(1);
        $originalVersion = $product->getVersion();
        $updateData = $this->createUpdateDataFromProduct($product);
        $response = $this->makeRequest('PUT', '/api/products', $updateData);
        $this->assertJsonResponse($response, Response::HTTP_ACCEPTED);
        $product = $this->productRepository->find(1);
        $this->assertEquals(
            $originalVersion + 1,
            $product->getVersion(),
            'The version field is not updated',
        );
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     measurements: array{
     *          weight: int,
     *          height: int,
     *          width: int,
     *          length: int
     *      },
     *     description: string,
     *     cost: int,
     *     tax: int,
     *     version: int
     * }
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

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     measurements: array{
     *          weight: int,
     *          height: int,
     *          width: int,
     *          length: int
     *      },
     *     description: null|string,
     *     cost: int,
     *     tax: int,
     *     version: int
     * }
     */
    private function createUpdateDataFromProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'measurements' => [
                'weight' => $product->getWeight(),
                'height' => $product->getHeight(),
                'width' => $product->getWidth(),
                'length' => $product->getLength(),
            ],
            'description' => $product->getDescription(),
            'cost' => $product->getPrice(),
            'tax' => $product->getTax(),
            'version' => $product->getVersion() + 1,
        ];
    }
}
