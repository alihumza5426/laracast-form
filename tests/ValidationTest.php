<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class ValidationTest extends TestCase
{
    /** @test */
    public function it_passes_when_data_meets_validation_rules()
    {
        $input = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $result = Forms::validate($input, [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $this->assertTrue($result['passed']);
        $this->assertEquals('Form validation passed.', $result['message']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals('John Doe', $result['data']['name']);
        $this->assertEquals('john@example.com', $result['data']['email']);
    }

    /** @test */
    public function it_fails_when_validation_rules_are_violated()
    {
        $input = [
            'name' => '',
            'email' => 'not-an-email',
        ];

        $result = Forms::validate($input, [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $this->assertFalse($result['passed']);
        $this->assertEquals('Form validation failed.', $result['message']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    /** @test */
    public function it_supports_custom_messages_and_attributes()
    {
        $input = [
            'age' => 15,
        ];

        $result = Forms::validate(
            $input,
            ['age' => ['required', 'min:18']],
            ['age.min' => 'You must be at least 18 years old.']
        );

        $this->assertFalse($result['passed']);
        $this->assertStringContainsString('You must be at least 18 years old.', $result['errors']['age'][0]);
    }
}
