<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;

/**
 * Middleware to merge database config with file config on every request.
 */
class ConfigManagerMiddleware implements MiddlewareInterface
{
    /**
     * Inject services.
     *
     * @param ConfigManager $configManager
     * @param Config        $config
     */
    public function __construct(
        protected ConfigManager $configManager,
        protected Config $config
    ) {
    }

    /**
     * Invoke the ConfigManager middleware, merging the db config with the file based one.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->config->mergeItems(null, $this->configManager->fetch());

        return $handler->handle($request);
    }
}
