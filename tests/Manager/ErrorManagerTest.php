<?php

namespace App\Tests\Manager;

use Twig\Environment;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ErrorManagerTest
 *
 * Test cases for error manager
 *
 * @package App\Tests\Manager
 */
class ErrorManagerTest extends TestCase
{
    /**
     * Test handle error exception
     *
     * @return void
     */
    public function testHandleErrorException(): void
    {
        // create twig mock
        /** @var Environment&\PHPUnit\Framework\MockObject\MockObject $twigMock */
        $twigMock = $this->createMock(Environment::class);

        // create error manager instance
        $errorManager = new ErrorManager($twigMock);

        // expect exception to be thrown
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Page not found');
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);

        // call tested method
        $errorManager->handleError('Page not found', Response::HTTP_NOT_FOUND);
    }
}
