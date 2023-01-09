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
final class SchemaNotFoundException extends UserFacingException
{
    protected string $title = 'ERROR.SCHEMA_NOT_FOUND.TITLE';
    protected int $httpCode = 404;
    protected string $slug;

    public function getDescription(): string|UserMessage
    {
        return new UserMessage('ERROR.SCHEMA_NOT_FOUND.DESCRIPTION', ['schema' => $this->slug]);
    }

    /**
     * Set schema slug.
     *
     * @param string $slug
     *
     * @return self
     */
    public function setSchema(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
