<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Contracts\FormTest;
use Khan\Forms\Facades\Forms;

class SampleAdmissionForm implements FormTest
{
    public function handle(array $data): array
    {
        $validation = Forms::validate($data, [
            'student_name' => ['required', 'string'],
            'class_id' => ['required', 'numeric'],
        ]);

        if (!$validation['passed']) {
            return $validation;
        }

        return [
            'passed' => true,
            'message' => 'The form passed successfully.',
            'errors' => [],
            'data' => $validation['data'],
        ];
    }
}

class CustomFormClassTest extends TestCase
{
    /** @test */
    public function it_can_run_a_custom_form_class_directly()
    {
        $result = Forms::test(SampleAdmissionForm::class, [
            'student_name' => 'Sara Doe',
            'class_id' => 8,
        ]);

        $this->assertTrue($result['passed']);
        $this->assertEquals('The form passed successfully.', $result['message']);
        $this->assertEquals('Sara Doe', $result['data']['student_name']);
    }

    /** @test */
    public function it_can_register_and_run_a_custom_form_class_by_alias()
    {
        Forms::register('admission-form', SampleAdmissionForm::class);

        $result = Forms::run('admission-form', [
            'student_name' => 'Sara Doe',
            'class_id' => 8,
        ]);

        $this->assertTrue($result['passed']);
        $this->assertEquals('The form passed successfully.', $result['message']);
    }

    /** @test */
    public function it_returns_errors_when_custom_form_class_fails_validation()
    {
        $result = Forms::test(SampleAdmissionForm::class, [
            'student_name' => '',
            'class_id' => 'not-numeric',
        ]);

        $this->assertFalse($result['passed']);
        $this->assertArrayHasKey('student_name', $result['errors']);
        $this->assertArrayHasKey('class_id', $result['errors']);
    }
}
