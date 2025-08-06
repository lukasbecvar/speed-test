<?php

namespace App\Util;

/**
 * Class SecurityUtil
 *
 * Utility class for security methods
 *
 * @package App\Util
 */
class SecurityUtil
{
    /**
     * Escape special characters in string to prevent XSS attacks
     *
     * @param string $string The input string to escape
     *
     * @return string|null The escaped string or null on error
     */
    public function escapeString(string $string): ?string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
    }
}
