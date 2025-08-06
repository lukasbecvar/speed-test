<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ErrorControllerTest
 *
 * Test cases for error handling controller
 *
 * @package App\Tests
 */
class ErrorControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test render default error page
     *
     * @return void
     */
    public function testRenderDefaultErrorPage(): void
    {
        $this->client->request('GET', '/error');

        // assert response
        $this->assertSelectorTextContains('.error-page-msg', 'Unknown error, please contact the service administrator');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test block maintenance page rendering
     *
     * @return void
     */
    public function testBlockMaintenancePageRendering(): void
    {
        $this->client->request('GET', '/error?code=maintenance');

        // assert response
        $this->assertSelectorTextContains('.error-page-msg', 'Unknown error, please contact the service administrator');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test render Bad Request error (400)
     *
     * @return void
     */
    public function testRenderBadRequestErrorPage(): void
    {
        $this->client->request('GET', '/error?code=400');

        // assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('.error-page-msg', 'Error: Bad request');
    }

    /**
     * Test render Not Found error (404)
     *
     * @return void
     */
    public function testRenderNotFoundErrorPage(): void
    {
        $this->client->request('GET', '/error?code=404');

        // assert response
        $this->assertSelectorTextContains('.error-page-msg', 'Error: Page not found');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test render Too Many Requests error (429)
     *
     * @return void
     */
    public function testRenderTooManyRequestsErrorPage(): void
    {
        $this->client->request('GET', '/error?code=429');

        // assert response
        $this->assertSelectorTextContains('.error-page-msg', 'Error: Too Many Requests');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test render Internal Server Error (500)
     *
     * @return void
     */
    public function testRenderInternalServerErrorPage(): void
    {
        $this->client->request('GET', '/error?code=500');

        // assert response
        $this->assertSelectorTextContains('.error-page-msg', 'Internal Server Error');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
