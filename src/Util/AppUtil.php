<?php

namespace App\Util;

use Exception;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AppUtil
 *
 * AppUtil provides basic site-related methods
 *
 * @package App\Util
 */
class AppUtil
{
    private KernelInterface $kernelInterface;

    public function __construct(KernelInterface $kernelInterface)
    {
        $this->kernelInterface = $kernelInterface;
    }

    /**
     * Get environment variable value
     *
     * @param string $key The environment variable key
     *
     * @return string The environment variable value
     */
    public function getEnvValue(string $key): string
    {
        return $_ENV[$key];
    }

    /**
     * Get application root directory
     *
     * @return string The application root directory
     */
    public function getAppRootDir(): string
    {
        return $this->kernelInterface->getProjectDir();
    }

    /**
     * Check if connection is secure (SSL)
     *
     * @return bool Whether the connection is secure
     */
    public function isSsl(): bool
    {
        // check if HTTPS header is set and its value is either 1 or 'on'
        return isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 1 || strtolower($_SERVER['HTTPS']) === 'on');
    }

    /**
     * Check if the application is in maintenance mode
     *
     * @return bool Whether the application is in maintenance mode
     */
    public function isMaintenance(): bool
    {
        return $this->getEnvValue('MAINTENANCE_MODE') === 'true';
    }

    /**
     * Check if ssl only mode is enabled
     *
     * @return bool Whether the application is under ssl only mode
     */
    public function isSSLOnly(): bool
    {
        return $this->getEnvValue('SSL_ONLY') === 'true';
    }

    /**
     * Check if the application is in development mode
     *
     * @return bool Whether the application is in development mode
     */
    public function isDevMode(): bool
    {
        // get env mode
        $envMode = $this->getEnvValue('APP_ENV');

        // check mode
        if ($envMode == 'dev' || $envMode == 'test') {
            return true;
        }

        return false;
    }

    /**
     * Get config from yaml file
     *
     * @param string $configFile The config file name
     *
     * @return mixed The config data
     */
    public function getYamlConfig(string $configFile): mixed
    {
        return Yaml::parseFile($this->getAppRootDir() . '/config/' . $configFile);
    }

    /**
     * Update the environment variable value
     *
     * @param string $key The environment variable key
     * @param string $value The new environment variable value
     *
     * @throws Exception If the environment value can't be updated
     */
    public function updateEnvValue(string $key, string $value): void
    {
        // get base .env file
        $mainEnvFile = $this->getAppRootDir() . '/.env';

        // check if .env file exists
        if (!file_exists($mainEnvFile)) {
            throw new Exception('.env file not found');
        }

        // load base .env file content
        $mainEnvContent = file_get_contents($mainEnvFile);
        if ($mainEnvContent === false) {
            throw new Exception('Failed to read .env file');
        }

        // load current environment name
        if (preg_match('/^APP_ENV=(\w+)$/m', $mainEnvContent, $matches)) {
            $env = $matches[1];
        } else {
            throw new Exception('APP_ENV not found in .env file');
        }

        // get current environment file
        $envFile = $this->getAppRootDir() . '/.env.' . $env;

        // check if current environment file exists
        if (!file_exists($envFile)) {
            throw new Exception('.env.' . $env . 'file not found');
        }

        // get current environment content
        $envContent = file_get_contents($envFile);

        // check if current environment loaded correctly
        if ($envContent === false) {
            throw new Exception('Failed to read .env. ' . $env . ' file');
        }

        try {
            if (preg_match('/^' . $key . '=.*/m', $envContent, $matches)) {
                $newEnvContent = preg_replace('/^' . $key . '=.*/m', "$key=$value", $envContent);

                // write new content to the environment file
                if (file_put_contents($envFile, $newEnvContent) === false) {
                    throw new Exception('Failed to write to .env ' . $env . ' file');
                }
            } else {
                throw new Exception($key . ' not found in .env file');
            }
        } catch (Exception $e) {
            throw new Exception('Error to update environment variable: ' . $e->getMessage());
        }
    }
}
