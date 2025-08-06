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
    public function testLoadIndex(): void
    {
        $this->client->request('GET', '/');

        // check response status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
