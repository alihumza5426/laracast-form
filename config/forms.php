<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Forms Dev Testing UI Status
    |--------------------------------------------------------------------------
    |
    | When enabled, developers can access a web-based form testing interface
    | to test registered form closures and custom form classes interactively.
    | By default, this is disabled in production environments.
    |
    */

    'enabled' => env('FORMS_DEV_UI_ENABLED', env('APP_DEBUG', true) && env('APP_ENV') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Define the routing options for the dev-only form testing dashboard.
    |
    */

    'routes' => [
        'prefix' => env('FORMS_ROUTE_PREFIX', 'forms'),
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Form Classes
    |--------------------------------------------------------------------------
    |
    | You can list custom form classes implementing Khan\Forms\Contracts\FormTest
    | here. They will automatically be discovered and made available in the
    | Forms facade, Artisan commands, and the interactive web testing UI.
    |
    */

    'forms' => [
        // 'student-admission' => \App\Forms\StudentAdmissionForm::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Response Messages
    |--------------------------------------------------------------------------
    |
    | Standard messages returned on pass and fail validation states.
    |
    */

    'messages' => [
        'passed' => 'Form validation passed.',
        'failed' => 'Form validation failed.',
        'not_found' => 'The requested form [:name] was not found in the registry.',
    ],

];
