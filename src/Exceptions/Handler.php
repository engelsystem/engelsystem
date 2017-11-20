<?php

namespace Engelsystem\Exceptions;

abstract class Handler
{
    /** @var string */
    protected $environment;

    const ENV_PRODUCTION = 'prod';
    const ENV_DEVELOPMENT = 'dev';

    /**
     * Handler constructor.
     *
     * @param string $environment production|development
     */
    public function __construct($environment = self::ENV_PRODUCTION)
    {
        $this->environment = $environment;
    }

    /**
     * Activate the error handler
     */
    public function register()
    {
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
