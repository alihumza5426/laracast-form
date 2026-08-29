<?php

namespace Khan\Forms\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class FormResult implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @var bool
     */
    public bool $passed;

    /**
     * @var string
     */
    public string $message;

    /**
     * @var array<string, mixed>
     */
    public array $errors;

    /**
     * @var array<string, mixed>
     */
    public array $data;

    /**
     * Create a new FormResult instance.
     *
     * @param bool $passed
     * @param string $message
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $data
     */
    public function __construct(
        bool $passed = true,
        string $message = '',
        array $errors = [],
        array $data = []
    ) {
        $this->passed = $passed;
        $this->message = $message;
        $this->errors = $errors;
        $this->data = $data;
    }

    /**
     * Create a successful form result.
     *
     * @param string $message
     * @param array<string, mixed> $data
     * @param array<string, mixed> $extra
     * @return static
     */
    public static function pass(string $message = 'Form validation passed.', array $data = [], array $extra = []): static
    {
        return new static(true, $message, [], array_merge($data, $extra));
    }

    /**
     * Create a failed form result.
     *
     * @param string $message
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $data
     * @return static
     */
    public static function fail(string $message = 'Form validation failed.', array $errors = [], array $data = []): static
    {
        return new static(false, $message, $errors, $data);
    }

    /**
     * Normalize any return payload into a consistent result structure.
     *
     * @param mixed $result
     * @param array<string, mixed> $fallbackData
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public static function normalize(mixed $result, array $fallbackData = []): array
    {
        if ($result instanceof self) {
            return $result->toArray();
        }

        if (is_bool($result)) {
            return [
                'passed' => $result,
                'message' => $result ? 'Form test passed.' : 'Form test failed.',
                'errors' => [],
                'data' => $fallbackData,
            ];
        }

        if (is_array($result)) {
            return [
                'passed' => (bool) ($result['passed'] ?? ($result['status'] ?? true)),
                'message' => (string) ($result['message'] ?? ($result['passed'] ?? true ? 'Form test passed.' : 'Form test failed.')),
                'errors' => (array) ($result['errors'] ?? []),
                'data' => (array) ($result['data'] ?? $fallbackData),
            ];
        }

        return [
            'passed' => true,
            'message' => 'Form executed successfully.',
            'errors' => [],
            'data' => $fallbackData,
        ];
    }

    /**
     * Get the instance as an array.
     *
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'message' => $this->message,
            'errors' => $this->errors,
            'data' => $this->data,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
