<?php

namespace Khan\Forms\Tests;

require_once __DIR__ . '/TestCase.php';

use Illuminate\Support\Facades\File;
use Khan\Forms\Facades\Forms;

class CommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        $generatedFile = app_path('Forms/GeneratedTestForm.php');
        if (File::exists($generatedFile)) {
            File::delete($generatedFile);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_a_new_form_class_via_artisan_command()
    {
        $this->artisan('make:form', ['name' => 'GeneratedTestForm'])
            ->assertExitCode(0);

        $expectedPath = app_path('Forms/GeneratedTestForm.php');
        $this->assertTrue(File::exists($expectedPath), "Expected file [{$expectedPath}] does not exist.");

        $content = File::get($expectedPath);
        $this->assertStringContainsString('class GeneratedTestForm implements FormTest', $content);
        $this->assertStringContainsString('use Khan\Forms\Contracts\FormTest;', $content);
    }

    /** @test */
    public function it_can_execute_form_test_command_for_registered_form()
    {
        Forms::register('cli-test-form', function (array $data) {
            return [
                'passed' => !empty($data['username']),
                'message' => !empty($data['username']) ? 'User test passed.' : 'Username required.',
                'errors' => empty($data['username']) ? ['username' => ['The username field is required.']] : [],
                'data' => $data,
            ];
        });

        $this->artisan('forms:test', [
            'form' => 'cli-test-form',
            '--data' => '{"username":"testuser"}',
        ])
        ->expectsOutputToContain('[STATUS] PASSED')
        ->assertExitCode(0);

        $this->artisan('forms:test', [
            'form' => 'cli-test-form',
            '--data' => '{}',
        ])
        ->expectsOutputToContain('[STATUS] FAILED')
        ->assertExitCode(1);
    }
}
