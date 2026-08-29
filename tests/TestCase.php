<?php

namespace Khan\Forms\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Khan\Forms\Facades\Forms;
use Khan\Forms\FormsServiceProvider;

if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    abstract class BaseTestCase extends \Orchestra\Testbench\TestCase {}
} elseif (class_exists(\Tests\TestCase::class)) {
    abstract class BaseTestCase extends \Tests\TestCase {}
} else {
    abstract class BaseTestCase extends \Illuminate\Foundation\Testing\TestCase
    {
        use \Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;

        public function createApplication()
        {
            $app = require __DIR__ . '/../../../../bootstrap/app.php';
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            return $app;
        }
    }
}

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FormsServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Forms' => Forms::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:6Cu/ozMDhuCQ6W9N12W3lO+i/kXfW0H+H4+Uu+4hAek=');
        $app['config']->set('forms.enabled', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['forms.enabled' => true]);
        $this->setUpDatabase();
    }

    /**
     * Set up in-memory database tables for tests.
     */
    protected function setUpDatabase(): void
    {
        // 1. student_fee_managers
        if (!Schema::hasTable('student_fee_managers')) {
            Schema::create('student_fee_managers', function (Blueprint $table) {
                $table->id();
                $table->string('student_id')->nullable();
                $table->string('student_name')->nullable();
                $table->string('title')->nullable();
                $table->string('challan_no')->nullable();
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('fee', 10, 2)->default(0);
                $table->decimal('fine', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->string('status')->default('unpaid');
                $table->string('type')->nullable();
                $table->string('fee_month')->nullable();
                $table->date('due_date')->nullable();
                $table->date('paid_date')->nullable();
                $table->timestamps();
            });

            // Seed sample fee record
            DB::table('student_fee_managers')->insert([
                'student_id' => '101',
                'student_name' => 'John Doe',
                'title' => 'Monthly Tuition Fee',
                'challan_no' => 'CH-9921',
                'total_amount' => 5000.00,
                'fee' => 5000.00,
                'fine' => 0.00,
                'discount' => 0.00,
                'status' => 'paid',
                'type' => 'student',
                'fee_month' => '2026-06',
                'due_date' => '2026-06-15',
                'created_at' => '2026-06-15 10:00:00',
                'updated_at' => '2026-06-15 10:00:00',
            ]);
        }

        // 2. students
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('roll_no')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();
            });

            DB::table('students')->insert([
                'id' => 101,
                'name' => 'John Doe',
                'roll_no' => 'R-101',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'created_at' => '2026-01-01 00:00:00',
            ]);
        }

        // 3. expenses
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('category')->nullable();
                $table->date('date')->nullable();
                $table->string('status')->default('paid');
                $table->text('description')->nullable();
                $table->timestamps();
            });

            DB::table('expenses')->insert([
                'title' => 'Office Supplies',
                'amount' => 250.00,
                'category' => 'Supplies',
                'date' => '2026-06-15',
                'status' => 'paid',
                'description' => 'Notebooks and pens',
                'created_at' => '2026-06-15 10:00:00',
            ]);
        }

        // 4. taskboards
        if (!Schema::hasTable('taskboards')) {
            Schema::create('taskboards', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('status')->default('pending');
                $table->string('priority')->default('medium');
                $table->string('assigned_to')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamps();
            });

            DB::table('taskboards')->insert([
                'title' => 'Prepare Exam Schedule',
                'status' => 'in_progress',
                'priority' => 'high',
                'assigned_to' => 'Admin',
                'due_date' => '2026-07-01',
                'created_at' => '2026-06-15 10:00:00',
            ]);
        }
    }
}
