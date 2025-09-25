<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reporting Engine Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Reporting Engine
    | package. You can customize various aspects of the reporting system here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the database tables and connections used by the
    | reporting engine.
    |
    */
    'database' => [
        'connection' => env('REPORTING_ENGINE_DB_CONNECTION', null),
        'tables' => [
            'reports' => 'reports',
            'report_fields' => 'report_fields',
            'report_models' => 'report_models',
            'report_data' => 'report_data',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for reports behavior and defaults.
    |
    */
    'reports' => [
        'default_per_page' => 25,
        'max_per_page' => 1000,
        'cache_ttl' => 3600, // Cache reports for 1 hour
        'allowed_field_types' => [
            'string',
            'integer',
            'decimal',
            'boolean',
            'date',
            'datetime',
            'text',
            'json',
        ],
        'export_formats' => [
            'csv',
            'excel',
            'pdf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the user interface components.
    |
    */
    'ui' => [
        'theme' => 'default',
        'use_tailwind' => true,
        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d H:i:s',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for the reporting engine.
    |
    */
    'security' => [
        'middleware' => ['web', 'auth'],
        'permissions' => [
            'view_reports' => 'view-reports',
            'create_reports' => 'create-reports',
            'edit_reports' => 'edit-reports',
            'delete_reports' => 'delete-reports',
            'export_reports' => 'export-reports',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Registration
    |--------------------------------------------------------------------------
    |
    | Models that can be used in reports. Add your models here to make them
    | available for report generation.
    |
    */
    'models' => [
        // Example: 'users' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for package routes.
    |
    */
    'routes' => [
        'prefix' => 'reporting',
        'middleware' => ['web', 'auth'],
    ],
];