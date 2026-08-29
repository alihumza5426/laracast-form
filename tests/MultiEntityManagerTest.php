<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class MultiEntityManagerTest extends TestCase
{
    /** @test */
    public function it_can_fetch_entries_for_students_expenses_and_taskboards()
    {
        // 1. Students
        $studentsResult = Forms::getEntries('students');
        $this->assertTrue($studentsResult['passed']);
        $this->assertGreaterThanOrEqual(1, $studentsResult['count']);

        // 2. Expenses
        $expensesResult = Forms::getEntries('expenses');
        $this->assertTrue($expensesResult['passed']);
        $this->assertGreaterThanOrEqual(1, $expensesResult['count']);

        // 3. Taskboards
        $tasksResult = Forms::getEntries('taskboards');
        $this->assertTrue($tasksResult['passed']);
        $this->assertGreaterThanOrEqual(1, $tasksResult['count']);
    }

    /** @test */
    public function it_can_fetch_and_update_an_entry_by_id()
    {
        $entry = Forms::getEntryById('expenses', 1);
        $this->assertTrue($entry['passed']);
        $this->assertEquals('Office Supplies', $entry['data']['title']);

        // Update
        $updated = Forms::updateEntry('expenses', 1, [
            'title' => 'Updated Office Supplies',
            'amount' => 300.00,
        ]);
        $this->assertTrue($updated['passed']);
        $this->assertEquals('Updated Office Supplies', $updated['data']['title']);
        $this->assertEquals(300.00, $updated['data']['amount']);
    }

    /** @test */
    public function it_can_delete_single_and_bulk_entries_for_different_entities()
    {
        // Insert test entries in taskboards
        \DB::table('taskboards')->insert([
            ['id' => 991, 'title' => 'Test Task 1', 'status' => 'pending', 'created_at' => now()],
            ['id' => 992, 'title' => 'Test Task 2', 'status' => 'pending', 'created_at' => now()],
            ['id' => 993, 'title' => 'Test Task 3', 'status' => 'pending', 'created_at' => now()],
        ]);

        // Single delete
        $delResult = Forms::deleteEntry('taskboards', 991);
        $this->assertTrue($delResult['passed']);
        $this->assertNull(\DB::table('taskboards')->where('id', 991)->first());

        // Bulk delete
        $bulkDelResult = Forms::deleteBulkEntries('taskboards', [992, 993]);
        $this->assertTrue($bulkDelResult['passed']);
        $this->assertEquals(2, $bulkDelResult['count']);
        $this->assertNull(\DB::table('taskboards')->where('id', 992)->first());
        $this->assertNull(\DB::table('taskboards')->where('id', 993)->first());
    }

    /** @test */
    public function it_filters_expenses_and_students_by_keyword()
    {
        $filtered = Forms::getEntries('students', null, 10, null, [
            'search' => 'John',
        ]);
        $this->assertTrue($filtered['passed']);
        $this->assertGreaterThanOrEqual(1, $filtered['count']);
    }
}
