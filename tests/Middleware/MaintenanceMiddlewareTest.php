<?php

namespace App\Tests\Middleware;

use App\Util\AppUtil;
use Psr\Log\LoggerInterface;
use App\Manager\ErrorManager;
use PHPUnit\Framework\TestCase;
use App\Middleware\MaintenanceMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class MaintenanceMiddlewareTest
 *
 * Test cases for maintenance middleware
 *
 * @package App\Tests\Middleware
 */
class MaintenanceMiddlewareTest extends TestCase
{
    private MaintenanceMiddleware $middleware;
    private AppUtil & MockObject $appUtilMock;
    private LoggerInterface & MockObject $loggerMock;
    private ErrorManager & MockObject $errorManagerMock;

    protected function setUp(): void
    {
        // mock dependencies
        $this->appUtilMock = $this->createMock(AppUtil::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->errorManagerMock = $this->createMock(ErrorManager::class);

        // create maintenance middleware instance
        $this->middleware = new MaintenanceMiddleware(
            $this->appUtilMock,
            $this->loggerMock,
            $this->errorManagerMock
        );
    }

    /**
     * Test handle maintenance mode when maintenance mode is enabled
     *
     * @return void
     */
    public function testHandleMaintenanceModeWhenModeEnabled(): void
    {
        // mock maintenance check call
        $this->appUtilMock->expects($this->once())->method('isMaintenance')->willReturn(true);

        // create a mock request event
        /** @var RequestEvent&MockObject $event */
        $event = $this->createMock(RequestEvent::class);

        // expect the error manager call
        $this->errorManagerMock->expects($this->once())
            ->method('getErrorView')->with('maintenance')->willReturn('Maintenance Mode Content');

        // expect response to be set
        $event->expects($this->once())
            ->method('setResponse')->with($this->callback(function ($response) {
                return $response instanceof Response &&
                    $response->getStatusCode() === 503 &&
                    $response->getContent() === 'Maintenance Mode Content';
            }));

        // call tested method
        $this->middleware->onKernelRequest($event);
    }

    /**
     * Test handle maintenance mode when maintenance mode is disabled
     *
     * @return void
     */
    public function testHandleMaintenanceModeWhenModeDisabled(): void
    {
        // mock maintenance check call
        $this->appUtilMock->expects($this->once())->method('isMaintenance')->willReturn(false);

        // create a mock request event
        /** @var RequestEvent&MockObject $event */
        $event = $this->createMock(RequestEvent::class);

        // expect the error manager not to be called
        $this->errorManagerMock->expects($this->never())->method('handleError');

        // expect response not to be set
        $event->expects($this->never())->method('setResponse');

        // call tested method
        $this->middleware->onKernelRequest($event);
    }
}
