<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class RegistrationTest extends TestCase
{
    /** @test */
    public function it_can_register_a_closure_form()
    {
        Forms::register('admission', function (array $data) {
            return [
                'passed' => isset($data['student_name']),
                'message' => 'Admission status checked.',
            ];
        });

        $this->assertTrue(Forms::has('admission'));
        $all = Forms::all();
        $this->assertArrayHasKey('admission', $all);
        $this->assertEquals('closure', $all['admission']['type']);
    }

    /** @test */
    public function it_can_register_with_custom_metadata()
    {
        Forms::register('scholarship', function (array $data) {
            return ['passed' => true];
        }, ['description' => 'Scholarship test workflow', 'category' => 'financial']);

        $all = Forms::all();
        $this->assertArrayHasKey('scholarship', $all);
        $this->assertEquals('Scholarship test workflow', $all['scholarship']['metadata']['description']);
    }

    /** @test */
    public function it_supports_adding_custom_methods_via_macros()
    {
        Forms::macro('quickEmailCheck', function (string $email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        });

        $this->assertTrue(Forms::hasMacro('quickEmailCheck'));
        $this->assertTrue(Forms::quickEmailCheck('test@domain.com'));
        $this->assertFalse(Forms::quickEmailCheck('invalid-email'));
    }
}
