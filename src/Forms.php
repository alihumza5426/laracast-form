<?php

namespace Khan\Forms;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Traits\Macroable;
use Khan\Forms\Contracts\FormRegistryInterface;
use Khan\Forms\Contracts\FormTest;
use Khan\Forms\Exceptions\FormNotFoundException;
use Khan\Forms\Exceptions\InvalidFormHandlerException;
use Khan\Forms\Results\FormResult;

class Forms implements FormRegistryInterface
{
    use Macroable;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected Container $container;

    /**
     * The validation factory instance.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected ValidationFactory $validatorFactory;

    /**
     * The registered form handlers and metadata.
     *
     * @var array<string, array{handler: callable|string, metadata: array<string, mixed>}>
     */
    protected array $registry = [];

    /**
     * Known table aliases for entity types.
     *
     * @var array<string, array<string>>
     */
    protected array $typeTableCandidates = [
        'fees' => ['student_fee_managers', 'student_fee_manager', 'student_fees', 'fee_managers', 'fees'],
        'students' => ['students', 'student', 'users'],
        'expenses' => ['expenses', 'expense', 'expense_managers', 'expense_manager'],
        'taskboards' => ['taskboards', 'task_boards', 'taskboard', 'tasks', 'task', 'task_manager'],
    ];

    /**
     * Create a new Forms instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Illuminate\Contracts\Validation\Factory $validatorFactory
     */
    public function __construct(Container $container, ValidationFactory $validatorFactory)
    {
        $this->container = $container;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Resolve the database table name for a given entity type.
     *
     * @param string $type ('fees', 'students', 'expenses', 'taskboards', etc.)
     * @return string|null
     */
    public function resolveTableForType(string $type = 'fees'): ?string
    {
        $normalized = strtolower(trim($type));
        
        // Handle singular/plural mappings
        if ($normalized === 'fee' || $normalized === 'challan') {
            $normalized = 'fees';
        } elseif ($normalized === 'student') {
            $normalized = 'students';
        } elseif ($normalized === 'expense') {
            $normalized = 'expenses';
        } elseif ($normalized === 'taskboard' || $normalized === 'task' || $normalized === 'tasks') {
            $normalized = 'taskboards';
        }

        $candidates = $this->typeTableCandidates[$normalized] ?? [$normalized, $normalized . 's'];

        foreach ($candidates as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Backward-compatible helper to resolve student fee table name.
     *
     * @return string|null
     */
    public function resolveTargetFeeTable(): ?string
    {
        return $this->resolveTableForType('fees');
    }

    /**
     * Fetch entries for any entity type (fees, students, expenses, taskboards) with filters.
     *
     * @param string $entityType Entity type (fees, students, expenses, taskboards)
     * @param string|null $date Date in YYYY-MM-DD format
     * @param int $limit Number of entries to retrieve
     * @param string|null $dateColumn Specific date column to filter
     * @param array<string, mixed> $filters Extra filters: student_id, type, status, search, category
     * @return array{passed: bool, entity: string, table: ?string, message: string, count: int, date: ?string, filters: array, errors: array, data: array}
     */
    public function getEntries(
        string $entityType = 'fees',
        ?string $date = null,
        int $limit = 10,
        ?string $dateColumn = null,
        array $filters = []
    ): array {
        $tableName = $this->resolveTableForType($entityType);

        if (!$tableName) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'table' => null,
                'message' => "Database table for [{$entityType}] was not found.",
                'count' => 0,
                'date' => $date,
                'filters' => $filters,
                'errors' => ['table' => ["Table for {$entityType} does not exist in the database."]],
                'data' => [],
            ];
        }

        $result = $this->fetchLatestByDate($tableName, $date, $limit, $dateColumn, ['*'], $filters);
        $result['entity'] = $entityType;
        $result['table'] = $tableName;

        return $result;
    }

    /**
     * Backward-compatible fetch for student fee entries.
     *
     * @param string|null $date Date in YYYY-MM-DD format
     * @param int $limit Number of entries to retrieve
     * @param string|null $dateColumn Specific date column to filter
     * @param array<string, mixed> $filters Extra filters
     * @return array{passed: bool, message: string, count: int, date: ?string, filters: array, errors: array, data: array}
     */
    public function getStudentFeeEntries(
        ?string $date = null,
        int $limit = 10,
        ?string $dateColumn = null,
        array $filters = []
    ): array {
        return $this->getEntries('fees', $date, $limit, $dateColumn, $filters);
    }

    /**
     * Fetch a single entry by ID for any entity type.
     *
     * @param string $entityType
     * @param int|string $id
     * @return array{passed: bool, entity: string, table: ?string, message: string, data: ?array}
     */
    public function getEntryById(string $entityType, int|string $id): array
    {
        $tableName = $this->resolveTableForType($entityType);
        if (!$tableName) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'table' => null,
                'message' => "Table for {$entityType} not found.",
                'data' => null,
            ];
        }

        $record = DB::table($tableName)->where('id', $id)->first();

        if (!$record) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'table' => $tableName,
                'message' => "Record #{$id} was not found in table [{$tableName}].",
                'data' => null,
            ];
        }

        return [
            'passed' => true,
            'entity' => $entityType,
            'table' => $tableName,
            'message' => "Record #{$id} found.",
            'data' => (array) $record,
        ];
    }

    /**
     * Update an entry by ID for any entity type.
     *
     * @param string $entityType
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return array{passed: bool, entity: string, message: string, id: int|string, data: array}
     */
    public function updateEntry(string $entityType, int|string $id, array $data): array
    {
        $tableName = $this->resolveTableForType($entityType);
        if (!$tableName) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => "Table for {$entityType} was not found in the database.",
                'id' => $id,
                'data' => [],
            ];
        }

        $availableColumns = Schema::getColumnListing($tableName);
        $updateData = [];

        // Only update columns that actually exist in the table, ignoring 'id' and '_token'
        foreach ($data as $key => $value) {
            if ($key === 'id' || $key === '_token' || $key === '_method') {
                continue;
            }
            if (in_array($key, $availableColumns, true)) {
                $updateData[$key] = $value;
            }
        }

        // Auto-update 'updated_at' if column exists and not provided
        if (in_array('updated_at', $availableColumns, true) && !isset($updateData['updated_at'])) {
            $updateData['updated_at'] = now();
        }

        if (empty($updateData)) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => 'No valid columns provided to update.',
                'id' => $id,
                'data' => [],
            ];
        }

        try {
            DB::table($tableName)->where('id', $id)->update($updateData);
            $updatedRecord = (array) DB::table($tableName)->where('id', $id)->first();

            return [
                'passed' => true,
                'entity' => $entityType,
                'message' => "Record #{$id} updated successfully.",
                'id' => $id,
                'data' => $updatedRecord,
            ];
        } catch (\Throwable $e) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => 'Update failed: ' . $e->getMessage(),
                'id' => $id,
                'data' => [],
            ];
        }
    }

    /**
     * Delete a single entry by ID from any entity table.
     *
     * @param string $entityType
     * @param int|string $id
     * @return array{passed: bool, entity: string, message: string, id: int|string}
     */
    public function deleteEntry(string $entityType, int|string $id): array
    {
        $tableName = $this->resolveTableForType($entityType);
        if (!$tableName) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => "Table for {$entityType} was not found in the database.",
                'id' => $id,
            ];
        }

        $deleted = DB::table($tableName)->where('id', $id)->delete();

        if ($deleted > 0) {
            return [
                'passed' => true,
                'entity' => $entityType,
                'message' => "Record #{$id} deleted successfully from [{$tableName}].",
                'id' => $id,
            ];
        }

        return [
            'passed' => false,
            'entity' => $entityType,
            'message' => "Record #{$id} could not be found or was already deleted.",
            'id' => $id,
        ];
    }

    /**
     * Delete multiple entries in bulk for any entity table.
     *
     * @param string $entityType
     * @param array<int|string> $ids
     * @return array{passed: bool, entity: string, message: string, count: int, ids: array}
     */
    public function deleteBulkEntries(string $entityType, array $ids): array
    {
        $tableName = $this->resolveTableForType($entityType);
        if (!$tableName) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => "Table for {$entityType} was not found in the database.",
                'count' => 0,
                'ids' => $ids,
            ];
        }

        $validIds = array_values(array_filter($ids, function ($val) {
            return !is_null($val) && $val !== '';
        }));

        if (empty($validIds)) {
            return [
                'passed' => false,
                'entity' => $entityType,
                'message' => 'No valid record IDs provided for deletion.',
                'count' => 0,
                'ids' => [],
            ];
        }

        $deleted = DB::table($tableName)->whereIn('id', $validIds)->delete();

        return [
            'passed' => true,
            'entity' => $entityType,
            'message' => "Successfully deleted {$deleted} record(s) from [{$tableName}].",
            'count' => $deleted,
            'ids' => $validIds,
        ];
    }

    /**
     * Backward-compatible delete single student fee entry.
     *
     * @param int|string $id
     * @return array{passed: bool, message: string, id: int|string}
     */
    public function deleteStudentFeeEntry(int|string $id): array
    {
        $result = $this->deleteEntry('fees', $id);
        return [
            'passed' => $result['passed'],
            'message' => $result['message'],
            'id' => $result['id'],
        ];
    }

    /**
     * Backward-compatible delete bulk student fee entries.
     *
     * @param array<int|string> $ids
     * @return array{passed: bool, message: string, count: int, ids: array}
     */
    public function deleteBulkStudentFeeEntries(array $ids): array
    {
        $result = $this->deleteBulkEntries('fees', $ids);
        return [
            'passed' => $result['passed'],
            'message' => $result['message'],
            'count' => $result['count'],
            'ids' => $result['ids'],
        ];
    }

    /**
     * Fetch list of students for student picker dropdown.
     *
     * @param string|null $search Optional search query
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getStudentsList(?string $search = null, int $limit = 100): array
    {
        try {
            // 1. Try 'students' table first if exists
            $studentTable = $this->resolveTableForType('students');
            if ($studentTable) {
                $cols = Schema::getColumnListing($studentTable);
                $idCol = in_array('id', $cols, true) ? 'id' : $cols[0];
                $nameCol = in_array('name', $cols, true) ? 'name' : (in_array('student_name', $cols, true) ? 'student_name' : (in_array('full_name', $cols, true) ? 'full_name' : null));
                $rollCol = in_array('roll_no', $cols, true) ? 'roll_no' : (in_array('registration_no', $cols, true) ? 'registration_no' : null);

                $selects = [$idCol . ' as id'];
                if ($nameCol) {
                    $selects[] = $nameCol . ' as name';
                }
                if ($rollCol) {
                    $selects[] = $rollCol . ' as roll_no';
                }

                $query = DB::table($studentTable)->select($selects);

                if (!empty($search)) {
                    $query->where(function ($sub) use ($search, $idCol, $nameCol, $rollCol) {
                        $sub->where($idCol, 'like', "%{$search}%");
                        if ($nameCol) {
                            $sub->orWhere($nameCol, 'like', "%{$search}%");
                        }
                        if ($rollCol) {
                            $sub->orWhere($rollCol, 'like', "%{$search}%");
                        }
                    });
                }

                return $query->limit($limit)->get()->map(function ($row) {
                    return (array) $row;
                })->toArray();
            }

            // 2. Fallback to distinct student_id from target fee table
            $targetTable = $this->resolveTargetFeeTable();
            if ($targetTable) {
                $cols = Schema::getColumnListing($targetTable);
                $hasStudentId = in_array('student_id', $cols, true);
                $hasStudentName = in_array('student_name', $cols, true);

                if ($hasStudentId) {
                    $query = DB::table($targetTable)
                        ->whereNotNull('student_id')
                        ->where('student_id', '!=', '')
                        ->distinct()
                        ->select('student_id as id');

                    if ($hasStudentName) {
                        $query->addSelect('student_name as name');
                    }

                    if (!empty($search)) {
                        $query->where(function ($sub) use ($search, $hasStudentName) {
                            $sub->where('student_id', 'like', "%{$search}%");
                            if ($hasStudentName) {
                                $sub->orWhere('student_name', 'like', "%{$search}%");
                            }
                        });
                    }

                    return $query->limit($limit)->get()->map(function ($row) {
                        return (array) $row;
                    })->toArray();
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback if database connection is not initialized
        }

        return [];
    }

    /**
     * Fetch latest entries from any table for a particular date and filters.
     *
     * @param string $tableName
     * @param string|null $date
     * @param int $limit
     * @param string|null $dateColumn
     * @param array<int, string> $columns
     * @param array<string, mixed> $filters
     * @return array{passed: bool, message: string, count: int, date: ?string, filters: array, errors: array, data: array}
     */
    public function fetchLatestByDate(
        string $tableName,
        ?string $date = null,
        int $limit = 10,
        ?string $dateColumn = null,
        array $columns = ['*'],
        array $filters = []
    ): array {
        if (!Schema::hasTable($tableName)) {
            return [
                'passed' => false,
                'message' => "Table [{$tableName}] does not exist.",
                'count' => 0,
                'date' => $date,
                'filters' => $filters,
                'errors' => ['table' => ["Table [{$tableName}] does not exist."]],
                'data' => [],
            ];
        }

        $availableColumns = Schema::getColumnListing($tableName);
        $colMap = array_combine(array_map('strtolower', $availableColumns), $availableColumns);

        $query = DB::table($tableName)->select($columns);

        // 1. Date Filter
        if (!empty($date)) {
            $formattedDate = date('Y-m-d', strtotime($date));

            if ($dateColumn && isset($colMap[strtolower($dateColumn)])) {
                $actualCol = $colMap[strtolower($dateColumn)];
                $query->where(function ($q) use ($actualCol, $formattedDate) {
                    $q->whereDate($actualCol, $formattedDate)
                      ->orWhere($actualCol, 'like', "{$formattedDate}%");
                });
            } else {
                // Search across candidate date columns
                $candidateDateCols = ['created_at', 'date', 'due_date', 'paid_date', 'fee_month', 'issue_date', 'updated_at', 'voucher_date', 'challan_date', 'start_date', 'end_date'];
                $matchedCols = [];
                foreach ($candidateDateCols as $c) {
                    if (isset($colMap[$c])) {
                        $matchedCols[] = $colMap[$c];
                    }
                }

                if (!empty($matchedCols)) {
                    $query->where(function ($q) use ($matchedCols, $formattedDate) {
                        foreach ($matchedCols as $idx => $c) {
                            if ($idx === 0) {
                                $q->whereDate($c, $formattedDate)
                                  ->orWhere($c, 'like', "{$formattedDate}%");
                            } else {
                                $q->orWhereDate($c, $formattedDate)
                                  ->orWhere($c, 'like', "{$formattedDate}%");
                            }
                        }
                    });
                }
            }
        }

        // 2. Student ID Filter
        $studentId = $filters['student_id'] ?? null;
        if (!empty($studentId)) {
            if (isset($colMap['student_id'])) {
                $query->where($colMap['student_id'], $studentId);
            } elseif (isset($colMap['student'])) {
                $query->where($colMap['student'], $studentId);
            } elseif (isset($colMap['id'])) {
                $query->where($colMap['id'], $studentId);
            }
        }

        // 3. Type Filter (challan, student, etc.)
        $type = $filters['type'] ?? null;
        if (!empty($type) && $type !== 'all') {
            $typeLower = strtolower($type);
            if (isset($colMap['type'])) {
                $query->whereRaw("LOWER({$colMap['type']}) = ?", [$typeLower]);
            } elseif ($typeLower === 'challan') {
                if (isset($colMap['challan_no'])) {
                    $query->whereNotNull($colMap['challan_no'])->where($colMap['challan_no'], '!=', '');
                } elseif (isset($colMap['title'])) {
                    $query->where('title', 'like', '%challan%');
                }
            } elseif ($typeLower === 'student') {
                if (isset($colMap['student_id'])) {
                    $query->whereNotNull($colMap['student_id'])->where($colMap['student_id'], '!=', '');
                }
            }
        }

        // 4. Status Filter
        $status = $filters['status'] ?? null;
        if (!empty($status) && $status !== 'all') {
            if (isset($colMap['status'])) {
                $query->whereRaw("LOWER({$colMap['status']}) = ?", [strtolower($status)]);
            }
        }

        // 5. Keyword Search Filter
        $search = $filters['search'] ?? null;
        if (!empty($search)) {
            $searchableCols = array_intersect(
                ['title', 'name', 'student_name', 'full_name', 'student_id', 'roll_no', 'challan_no', 'invoice_no', 'description', 'remarks', 'category', 'task_name', 'id', 'email', 'phone'],
                array_keys($colMap)
            );
            if (!empty($searchableCols)) {
                $query->where(function ($q) use ($searchableCols, $colMap, $search) {
                    foreach (array_values($searchableCols) as $idx => $key) {
                        $col = $colMap[$key];
                        if ($idx === 0) {
                            $q->where($col, 'like', "%{$search}%");
                        } else {
                            $q->orWhere($col, 'like', "%{$search}%");
                        }
                    }
                });
            }
        }

        // Order by latest ID or created_at
        if (isset($colMap['id'])) {
            $query->orderByDesc($colMap['id']);
        } elseif (isset($colMap['created_at'])) {
            $query->orderByDesc($colMap['created_at']);
        }

        $records = $query->limit($limit)->get();
        $count = $records->count();
        $dateLabel = $date ? " for date [{$date}]" : "";

        return [
            'passed' => true,
            'message' => "Successfully retrieved {$count} entry/entries from {$tableName}{$dateLabel}.",
            'count' => $count,
            'date' => $date,
            'filters' => $filters,
            'errors' => [],
            'data' => $records->toArray(),
        ];
    }

    /**
     * Validate data against specified rules using Laravel's validation engine.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $customAttributes
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        $validator = $this->makeValidator($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return FormResult::fail(
                $this->getConfigMessage('failed', 'Form validation failed.'),
                $validator->errors()->toArray(),
                $data
            )->toArray();
        }

        return FormResult::pass(
            $this->getConfigMessage('passed', 'Form validation passed.'),
            $validator->validated()
        )->toArray();
    }

    /**
     * Create an underlying Laravel Validator instance.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function makeValidator(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): ValidatorContract {
        return $this->validatorFactory->make($data, $rules, $messages, $customAttributes);
    }

    /**
     * Register a custom form test closure, invokable class, or FormTest class.
     *
     * @param string $name
     * @param callable|string $handler
     * @param array<string, mixed> $metadata
     * @return $this
     */
    public function register(string $name, callable|string $handler, array $metadata = []): self
    {
        $this->registry[$name] = [
            'name' => $name,
            'handler' => $handler,
            'metadata' => $metadata,
            'type' => is_string($handler) ? 'class' : 'closure',
        ];

        return $this;
    }

    /**
     * Check if a form is registered by name.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        if (isset($this->registry[$name])) {
            return true;
        }

        $configForms = (array) config('forms.forms', []);
        if (isset($configForms[$name])) {
            return true;
        }

        if (class_exists($name) && is_subclass_of($name, FormTest::class)) {
            return true;
        }

        return false;
    }

    /**
     * Get all registered forms including runtime registrations and configured classes.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $all = $this->registry;

        $configForms = (array) config('forms.forms', []);
        foreach ($configForms as $key => $class) {
            $name = is_string($key) ? $key : (is_string($class) ? class_basename($class) : (string)$key);
            if (!isset($all[$name])) {
                $all[$name] = [
                    'name' => $name,
                    'handler' => $class,
                    'metadata' => [
                        'source' => 'config',
                        'class' => is_string($class) ? $class : null,
                    ],
                    'type' => 'class',
                ];
            }
        }

        return $all;
    }

    /**
     * Execute a registered form handler or FormTest class.
     *
     * @param string $name
     * @param array<string, mixed> $data
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public function run(string $name, array $data = []): array
    {
        // 1. Check in-memory registry
        if (isset($this->registry[$name])) {
            return $this->executeHandler($this->registry[$name]['handler'], $data);
        }

        // 2. Check config forms
        $configForms = (array) config('forms.forms', []);
        if (isset($configForms[$name])) {
            return $this->executeHandler($configForms[$name], $data);
        }

        // 3. Check if $name is a class name directly or in App\Forms
        $resolvedClass = $this->resolveFormClassName($name);
        if ($resolvedClass !== null) {
            return $this->executeHandler($resolvedClass, $data);
        }

        // Form not found
        $notFoundMsg = str_replace(
            ':name',
            $name,
            $this->getConfigMessage('not_found', "The requested form [{$name}] was not found in the registry.")
        );

        return FormResult::fail(
            $notFoundMsg,
            ['form' => [$notFoundMsg]],
            $data
        )->toArray();
    }

    /**
     * Execute a form or throw a FormNotFoundException if it does not exist.
     *
     * @param string $name
     * @param array<string, mixed> $data
     * @return array{passed: bool, message: string, errors: array, data: array}
     *
     * @throws \Khan\Forms\Exceptions\FormNotFoundException
     */
    public function runOrFail(string $name, array $data = []): array
    {
        if (!$this->has($name)) {
            throw new FormNotFoundException($name);
        }

        return $this->run($name, $data);
    }

    /**
     * Execute a FormTest class instance or class string directly.
     *
     * @param string|FormTest $form
     * @param array<string, mixed> $data
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    public function test(string|FormTest $form, array $data = []): array
    {
        return $this->executeHandler($form, $data);
    }

    /**
     * Execute a handler (closure, FormTest instance, invokable, or class string).
     *
     * @param callable|string|FormTest $handler
     * @param array<string, mixed> $data
     * @return array{passed: bool, message: string, errors: array, data: array}
     */
    protected function executeHandler(callable|string|FormTest $handler, array $data): array
    {
        if ($handler instanceof FormTest) {
            $result = $handler->handle($data);
            return FormResult::normalize($result, $data);
        }

        if (is_callable($handler)) {
            $result = $this->container->call($handler, ['data' => $data]);
            return FormResult::normalize($result, $data);
        }

        if (is_string($handler)) {
            $instance = $this->container->make($handler);

            if ($instance instanceof FormTest) {
                $result = $instance->handle($data);
                return FormResult::normalize($result, $data);
            }

            if (is_callable($instance)) {
                $result = $this->container->call($instance, ['data' => $data]);
                return FormResult::normalize($result, $data);
            }

            throw new InvalidFormHandlerException($handler);
        }

        throw new InvalidFormHandlerException(gettype($handler));
    }

    /**
     * Attempt to resolve a class name from a string identifier.
     *
     * @param string $name
     * @return class-string|null
     */
    protected function resolveFormClassName(string $name): ?string
    {
        if (class_exists($name)) {
            return $name;
        }

        $appFormClass = 'App\\Forms\\' . ltrim($name, '\\');
        if (class_exists($appFormClass)) {
            return $appFormClass;
        }

        return null;
    }

    /**
     * Get a configured message with fallback.
     *
     * @param string $key
     * @param string $fallback
     * @return string
     */
    protected function getConfigMessage(string $key, string $fallback): string
    {
        return (string) config("forms.messages.{$key}", $fallback);
    }
}
