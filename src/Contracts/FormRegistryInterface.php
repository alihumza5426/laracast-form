<?php

namespace Khan\Forms\Contracts;

interface FormRegistryInterface
{
    /**
     * Register a custom form closure, invokable, or FormTest class.
     *
     * @param string $name
     * @param callable|string $handler
     * @param array<string, mixed> $metadata
     * @return $this
     */
    public function register(string $name, callable|string $handler, array $metadata = []): self;

    /**
     * Check if a form is registered by name.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Retrieve all registered form handlers with their metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array;

    /**
     * Execute a registered form handler with given data.
     *
     * @param string $name
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function run(string $name, array $data = []): array;
}
