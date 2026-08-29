<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class ExecutionTest extends TestCase
{
    /** @test */
    public function it_can_execute_a_registered_closure_and_normalize_result()
    {
        Forms::register('student-admission', function (array $data) {
            return [
                'passed' => isset($data['student_name'], $data['class_id']),
                'message' => 'Student admission form checked.',
                'data' => $data,
            ];
        });

        $passResult = Forms::run('student-admission', [
            'student_name' => 'Alice',
            'class_id' => '5',
        ]);

        $this->assertTrue($passResult['passed']);
        $this->assertEquals('Student admission form checked.', $passResult['message']);
        $this->assertEquals('Alice', $passResult['data']['student_name']);

        $failResult = Forms::run('student-admission', [
            'student_name' => 'Bob',
        ]);

        $this->assertFalse($failResult['passed']);
    }

    /** @test */
    public function it_normalizes_boolean_returns_from_closures()
    {
        Forms::register('simple-check', function (array $data) {
            return !empty($data['token']);
        });

        $passResult = Forms::run('simple-check', ['token' => 'secret123']);
        $this->assertTrue($passResult['passed']);
        $this->assertIsArray($passResult['data']);

        $failResult = Forms::run('simple-check', []);
        $this->assertFalse($failResult['passed']);
    }
}
