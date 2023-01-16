<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Exceptions;

use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Schema doesn't have the required format.
 */
final class BadSchemaException extends UserFacingException
{
    protected string $title = 'ERROR.BAD_SCHEMA.TITLE';
    protected string|UserMessage $description = 'ERROR.BAD_SCHEMA.DESCRIPTION';
}
