<?php

namespace Khan\Forms\Exceptions;

use Exception;

class FormNotFoundException extends Exception
{
    /**
     * The form name that was not found.
     *
     * @var string
     */
    protected string $formName;

    /**
     * Create a new exception instance.
     *
     * @param string $formName
     * @param string|null $message
     */
    public function __construct(string $formName, ?string $message = null)
    {
        $this->formName = $formName;
        parent::__construct($message ?? "The requested form [{$formName}] was not found in the registry.");
    }

    /**
     * Get the form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        return $this->formName;
    }
}
