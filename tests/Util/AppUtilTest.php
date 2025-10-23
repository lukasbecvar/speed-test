<?php

namespace App\Tests\Util;

use App\Util\AppUtil;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AppUtilTest
 *
 * Test cases for app util
 *
 * @package App\Tests\Util
 */
class AppUtilTest extends TestCase
{
    private AppUtil $appUtil;
    private KernelInterface & MockObject $kernelInterface;

    protected function setUp(): void
    {
        // mock dependencies
        $this->kernelInterface = $this->createMock(KernelInterface::class);

        // create app util instance
        $this->appUtil = new AppUtil($this->kernelInterface);
    }

    /**
     * Test get environment variable
     *
     * @return void
     */
    public function testGetEnvironmentVariable(): void
    {
        $result = $this->appUtil->getEnvValue('APP_ENV');
        $this->assertEquals($result, $_ENV['APP_ENV']);
    }

    /**
     * Test is SSL check
     *
     * @return void
     */
    public function testIsSslCheck(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue($this->appUtil->isSsl());

        $_SERVER['HTTPS'] = '1';
        $this->assertTrue($this->appUtil->isSsl());

        unset($_SERVER['HTTPS']);
        $this->assertFalse($this->appUtil->isSsl());
    }

    /**
     * Test check maintenance mode status
     *
     * @return void
     */
    public function testIsMaintenanceStatus(): void
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';
        $this->assertTrue($this->appUtil->isMaintenance());

        $_ENV['MAINTENANCE_MODE'] = 'false';
        $this->assertFalse($this->appUtil->isMaintenance());
    }

    /**
     * Test check SSL only mode status
     *
     * @return void
     */
    public function testIsSSLOnlyStatus(): void
    {
        $_ENV['SSL_ONLY'] = 'true';
        $this->assertTrue($this->appUtil->isSSLOnly());

        $_ENV['SSL_ONLY'] = 'false';
        $this->assertFalse($this->appUtil->isSSLOnly());
    }

    /**
     * Test check dev mode status
     *
     * @return void
     */
    public function testCheckDevModeStatus(): void
    {
        $_ENV['APP_ENV'] = 'dev';
        $this->assertTrue($this->appUtil->isDevMode());

        $_ENV['APP_ENV'] = 'test';
        $this->assertTrue($this->appUtil->isDevMode());

        $_ENV['APP_ENV'] = 'prod';
        $this->assertFalse($this->appUtil->isDevMode());
    }

    /**
     * Test update environment value updates target env file
     *
     * @return void
     */
    public function testUpdateEnvValueUpdatesTargetFile(): void
    {
        // mock testing .env
        $tempDir = sys_get_temp_dir() . '/app_util_' . uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir . '/.env', "APP_ENV=test\nAPP_SECRET=old-secret\n");
        file_put_contents($tempDir . '/.env.test', "APP_SECRET=old-secret\n");
        $this->kernelInterface->method('getProjectDir')->willReturn($tempDir);

        // call tested method
        $this->appUtil->updateEnvValue('APP_SECRET', 'new-secret');

        // assert result
        $updatedContent = file_get_contents($tempDir . '/.env.test') ?: '';
        $this->assertStringContainsString('APP_SECRET=new-secret', $updatedContent);

        // clean up
        unlink($tempDir . '/.env');
        unlink($tempDir . '/.env.test');
        rmdir($tempDir);
    }
}
