<?php

namespace App\Manager;

use Exception;
use Twig\Environment;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ErrorManager
 *
 * Manager for error handling
 *
 * @package App\Manager
 */
class ErrorManager
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Handle error exception
     *
     * @param string $msg The error message
     * @param int $code The error code
     *
     * @throws HttpException The error exception
     *
     * @return never
     */
    public function handleError(string $msg, int $code): void
    {
        throw new HttpException($code, $msg, null, [], $code);
    }

    /**
     * Get error view by http code
     *
     * @param string|int $code The error code
     *
     * @return string The error view twig render
     */
    public function getErrorView(string|int $code): string
    {
        try {
            return $this->twig->render('errors/error-' . $code . '.twig');
        } catch (Exception) {
            return $this->twig->render('errors/error-unknown.twig');
        }
    }
}
