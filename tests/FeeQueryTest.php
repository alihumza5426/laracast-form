<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class FeeQueryTest extends TestCase
{
    /** @test */
    public function it_can_fetch_student_fee_entries_via_facade()
    {
        $result = Forms::getStudentFeeEntries('2026-06-15', 10);

        $this->assertTrue($result['passed']);
        $this->assertIsArray($result['data']);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_can_fetch_students_list_via_facade()
    {
        $students = Forms::getStudentsList();
        $this->assertIsArray($students);
    }

    /** @test */
    public function it_can_filter_student_fee_entries_with_custom_filters()
    {
        $result = Forms::getStudentFeeEntries(null, 10, null, [
            'type' => 'student',
            'student_id' => '101',
            'status' => 'paid',
        ]);

        $this->assertTrue($result['passed']);
        $this->assertIsArray($result['data']);
    }
}
