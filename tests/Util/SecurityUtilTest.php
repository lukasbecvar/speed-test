<?php

namespace App\Tests\Util;

use App\Util\SecurityUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class SecurityUtilTest
 *
 * Test cases for security util
 *
 * @package App\Tests\Util
 */
class SecurityUtilTest extends TestCase
{
    private SecurityUtil $securityUtil;

    protected function setUp(): void
    {
        $_ENV['APP_SECRET'] = 'test_secret';

        // create instance of SecurityUtil
        $this->securityUtil = new SecurityUtil();
    }

    /**
     * Test escape XSS attacks
     *
     * @return void
     */
    public function testEscapeString(): void
    {
        $input = '<script>alert("xss")</script>';
        $expectedOutput = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';

        // assert result
        $this->assertEquals($expectedOutput, $this->securityUtil->escapeString($input));
    }
}
