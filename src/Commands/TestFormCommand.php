<?php

namespace Khan\Forms\Commands;

use Illuminate\Console\Command;
use Khan\Forms\Facades\Forms;

class TestFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:test 
                            {form : The name of the registered form or class (e.g. StudentAdmissionForm, student-admission)}
                            {--data= : JSON encoded input data payload or comma-separated key:value pairs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute and test a registered form closure or FormTest class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $form = (string) $this->argument('form');
        $rawPayload = $this->option('data');

        $data = $this->parseInputData($rawPayload);

        $this->info("Executing Form Test: <comment>{$form}</comment>");
        $this->line("Input Payload: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->newLine();

        $result = Forms::run($form, $data);

        if (!empty($result['passed'])) {
            $this->info("==========================================");
            $this->info(" [STATUS] PASSED");
            $this->info("==========================================");
        } else {
            $this->error("==========================================");
            $this->error(" [STATUS] FAILED");
            $this->error("==========================================");
        }

        $this->line("<comment>Message:</comment> " . ($result['message'] ?? 'N/A'));

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error("Validation / Execution Errors:");
            $errorRows = [];
            foreach ($result['errors'] as $field => $messages) {
                $errorRows[] = [
                    'Field' => $field,
                    'Messages' => is_array($messages) ? implode('; ', $messages) : (string) $messages,
                ];
            }
            $this->table(['Field', 'Messages'], $errorRows);
        }

        if (!empty($result['data'])) {
            $this->newLine();
            $this->line("<comment>Returned Data:</comment>");
            $this->line(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return !empty($result['passed']) ? 0 : 1;
    }

    /**
     * Parse raw string payload into an associative array.
     *
     * @param string|null $rawPayload
     * @return array<string, mixed>
     */
    protected function parseInputData(?string $rawPayload): array
    {
        if (empty($rawPayload)) {
            return [];
        }

        $trimmed = trim($rawPayload, " \t\n\r\0\x0B'\"");

        // Check if JSON
        if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Check key:value or key=value format (e.g. name=John,email=john@test.com)
        if (str_contains($trimmed, '=') || str_contains($trimmed, ':')) {
            $pairs = explode(',', $trimmed);
            $data = [];
            foreach ($pairs as $pair) {
                $separator = str_contains($pair, '=') ? '=' : ':';
                $parts = explode($separator, $pair, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $val = trim($parts[1]);
                    $data[$key] = is_numeric($val) ? (str_contains($val, '.') ? (float)$val : (int)$val) : $val;
                }
            }
            if (!empty($data)) {
                return $data;
            }
        }

        return [];
    }
}
