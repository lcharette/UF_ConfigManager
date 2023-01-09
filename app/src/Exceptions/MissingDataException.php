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

use Exception;
use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Schema not found exception. Will return a 404 error for the end user.
 */
final class MissingDataException extends UserFacingException
{
    protected string $title = 'ERROR.MISSING_DATA.TITLE';
    protected string|UserMessage $description = 'ERROR.MISSING_DATA.DESCRIPTION';
}
