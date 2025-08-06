<?php

namespace App\Tests\Middleware;

use App\Util\AppUtil;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use App\Middleware\SecurityCheckMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SecurityCheckMiddlewareTest
 *
 * Test cases for security check middleware
 *
 * @package App\Tests\Middleware
 */
class SecurityCheckMiddlewareTest extends TestCase
{
    private AppUtil & MockObject $appUtilMock;
    private SecurityCheckMiddleware $middleware;
    private ErrorManager & MockObject $errorManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create security check middleware instance
        $this->middleware = new SecurityCheckMiddleware(
            $this->appUtilMock,
            $this->errorManagerMock
        );
    }

    /**
     * Test SSL check passes
     *
     * @return void
     */
    public function testSslCheckPasses(): void
    {
        // mock SSL check enabled
        $this->appUtilMock->expects($this->once())->method('isSSLOnly')->willReturn(true);

        // mock SSL connection is secure
        $this->appUtilMock->expects($this->once())->method('isSsl')->willReturn(true);

        // expect error manager not to be called
        $this->errorManagerMock->expects($this->never())->method('handleError');

        // execute tested method
        $this->middleware->onKernelRequest();
    }

    /**
     * Test SSL check fail
     *
     * @return void
     */
    public function testSslCheckFail(): void
    {
        // mock SSL check enabled
        $this->appUtilMock->expects($this->once())->method('isSSLOnly')->willReturn(true);

        // mock SSL connection is not secure
        $this->appUtilMock->expects($this->once())->method('isSsl')->willReturn(false);

        // expect error manager call
        $this->errorManagerMock->expects($this->once())->method('handleError')->with(
            'SSL error: connection not running on ssl protocol',
            Response::HTTP_UPGRADE_REQUIRED
        );

        // execute tested method
        $this->middleware->onKernelRequest();
    }

    /**
     * Test SSL check disabled
     *
     * @return void
     */
    public function testSslCheckDisabled(): void
    {
        // mock SSL check disabled
        $this->appUtilMock->expects($this->once())->method('isSSLOnly')->willReturn(false);

        // expect no SSL check and no error handling called
        $this->appUtilMock->expects($this->never())->method('isSsl');

        // expect error manager not to be called
        $this->errorManagerMock->expects($this->never())->method('handleError');

        // execute tested method
        $this->middleware->onKernelRequest();
    }
}
