<?php

namespace App\Tests\Twig;

use App\Util\AppUtil;
use App\Twig\AppUtilExtension;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AppUtilExtensionTest
 *
 * Test cases for app util twig extension
 *
 * @package App\Tests\Twig
 */
class AppUtilExtensionTest extends TestCase
{
    private AppUtil & MockObject $appUtil;
    private AppUtilExtension $appUtilExtension;

    protected function setUp(): void
    {
        $this->appUtil = $this->getMockBuilder(AppUtil::class)->disableOriginalConstructor()->getMock();
        $this->appUtilExtension = new AppUtilExtension($this->appUtil);
    }

    /**
     * Test get functions
     *
     * @return void
     */
    public function testGetFunctions(): void
    {
        // call tested method
        $functions = $this->appUtilExtension->getFunctions();

        // assert result
        $this->assertCount(1, $functions);

        // check getEnvValue function
        $this->assertEquals('getEnvValue', $functions[0]->getName());
        $this->assertEquals([$this->appUtil, 'getEnvValue'], $functions[0]->getCallable());
    }
}
