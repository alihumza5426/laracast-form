<?php

namespace Khan\Forms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getStudentFeeEntries(?string $date = null, int $limit = 10, ?string $dateColumn = null, array $filters = [])
 * @method static array fetchLatestByDate(string $tableName, ?string $date = null, int $limit = 10, ?string $dateColumn = null, array $columns = ['*'], array $filters = [])
 * @method static array getStudentsList(?string $search = null, int $limit = 100)
 * @method static array deleteStudentFeeEntry(int|string $id)
 * @method static array deleteBulkStudentFeeEntries(array $ids)
 * @method static string|null resolveTargetFeeTable()
 * @method static array validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
 * @method static \Khan\Forms\Forms register(string $name, callable|string $handler, array $metadata = [])
 * @method static bool has(string $name)
 * @method static array all()
 * @method static array run(string $name, array $data = [])
 * @method static array runOrFail(string $name, array $data = [])
 * @method static array test(string|\Khan\Forms\Contracts\FormTest $form, array $data = [])
 * @method static \Illuminate\Contracts\Validation\Validator makeValidator(array $data, array $rules, array $messages = [], array $customAttributes = [])
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 *
 * @see \Khan\Forms\Forms
 */
class Forms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'khan.forms';
    }
}
