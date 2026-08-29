<?php

namespace Khan\Forms\Contracts;

interface FormTest
{
    /**
     * Handle the form test and validation logic.
     *
     * Expected return structure:
     * [
     *     'passed' => (bool),
     *     'message' => (string),
     *     'errors' => (array),
     *     'data' => (array),
     * ]
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function handle(array $data): array;
}
