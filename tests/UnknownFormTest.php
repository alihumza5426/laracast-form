<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Khan\Forms\Exceptions\FormNotFoundException;
use Khan\Forms\Facades\Forms;

class UnknownFormTest extends TestCase
{
    /** @test */
    public function it_returns_consistent_failed_structure_for_unknown_form()
    {
        $result = Forms::run('non-existent-form', ['foo' => 'bar']);

        $this->assertFalse($result['passed']);
        $this->assertStringContainsString('non-existent-form', $result['message']);
        $this->assertArrayHasKey('form', $result['errors']);
        $this->assertEquals(['foo' => 'bar'], $result['data']);
    }

    /** @test */
    public function it_throws_form_not_found_exception_when_using_run_or_fail()
    {
        $this->expectException(FormNotFoundException::class);
        $this->expectExceptionMessage('The requested form [missing-form] was not found');

        Forms::runOrFail('missing-form');
    }
}
