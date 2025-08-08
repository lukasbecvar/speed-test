<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IndexControllerTest
 *
 * Test cases for main page provider controller
 *
 * @package App\Tests
 */
class IndexControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Test load main page
     *
     * @return void
     */
    public function testLoadMainPage(): void
    {
        $this->client->request('GET', '/');

        // check response status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // check for specific content on the page
        $this->assertSelectorTextContains('h3', 'Download');
        $this->assertSelectorTextContains('button', 'Start');
        $this->assertSelectorTextContains('div', 'Press Start to begin');
    }

    /**
     * Test ping check endpoint
     *
     * @return void
     */
    public function testPingCheck(): void
    {
        $this->client->request('HEAD', '/ping');

        // check response status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test download speed check endpoint
     *
     * @return void
     */
    public function testDownloadSpeedCheck(): void
    {
        $this->client->request('GET', '/download');

        // check response status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/octet-stream');
    }

    /**
     * Test upload speed check endpoint
     *
     * @return void
     */
    public function testUploadSpeedCheck(): void
    {
        $this->client->request('POST', '/upload');

        // check response status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
