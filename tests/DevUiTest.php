<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Facades\Forms;

class DevUiTest extends TestCase
{
    /** @test */
    public function it_can_render_the_dev_testing_dashboard_when_enabled()
    {
        $this->withoutExceptionHandling();
        $response = $this->get(route('forms.test.index'));
        $response->assertStatus(200);
        $response->assertSee('Student Fee Manager');
    }

    /** @test */
    public function it_blocks_access_to_dashboard_when_disabled()
    {
        config(['forms.enabled' => false]);

        $response = $this->get(route('forms.test.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_execute_fee_entries_search_via_ajax_endpoint()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson(route('forms.test.search'), [
                'entity' => 'fees',
                'date' => '2026-06-15',
                'type' => 'student',
                'status' => 'all',
                'limit' => 10,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'passed',
            'message',
            'count',
            'data',
        ]);
    }

    /** @test */
    public function it_can_fetch_students_list_endpoint()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson(route('forms.test.students'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'passed',
            'data',
            'count',
        ]);
    }

    /** @test */
    public function it_can_show_and_update_entry_via_ajax_endpoints()
    {
        // 1. Show entry
        $showResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->getJson(route('forms.test.entry', ['id' => 1, 'entity' => 'fees']));
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('passed', true);

        // 2. Update entry
        $updateResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson(route('forms.test.update', ['id' => 1]), [
                'entity' => 'fees',
                'title' => 'Updated Term Fee',
                'fee' => 6000.00,
            ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('passed', true);
        $updateResponse->assertJsonPath('data.title', 'Updated Term Fee');
    }

    /** @test */
    public function it_can_delete_and_bulk_delete_via_ajax_endpoints()
    {
        // Insert dummy fees for delete test
        \DB::table('student_fee_managers')->insert([
            ['id' => 881, 'title' => 'Delete Test 1', 'status' => 'unpaid', 'created_at' => now()],
            ['id' => 882, 'title' => 'Delete Test 2', 'status' => 'unpaid', 'created_at' => now()],
            ['id' => 883, 'title' => 'Delete Test 3', 'status' => 'unpaid', 'created_at' => now()],
        ]);

        // Single delete
        $delResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->deleteJson(route('forms.test.delete', ['id' => 881]), [
                'entity' => 'fees',
            ]);
        $delResponse->assertStatus(200);
        $delResponse->assertJsonPath('passed', true);

        // Bulk delete
        $bulkResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson(route('forms.test.delete.bulk'), [
                'entity' => 'fees',
                'ids' => [882, 883],
            ]);
        $bulkResponse->assertStatus(200);
        $bulkResponse->assertJsonPath('passed', true);
        $bulkResponse->assertJsonPath('count', 2);
    }
}
