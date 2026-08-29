<?php

namespace Khan\Forms\Exceptions;

use InvalidArgumentException;

class InvalidFormHandlerException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     *
     * @param string $handler
     * @param string|null $message
     */
    public function __construct(string $handler, ?string $message = null)
    {
        parent::__construct($message ?? "Form handler [{$handler}] must be a callable or implement Khan\\Forms\\Contracts\\FormTest.");
    }
}
