# Reporting Engine for Laravel

A comprehensive Laravel package for building dynamic reports with Livewire 3.x components, Tailwind CSS styling, and MySQL optimization.

## Features

- 🚀 **Dynamic Report Generation**: Create reports from any Laravel model
- 📊 **Flexible Field Configuration**: Define sortable, searchable, and filterable fields
- 🎨 **Beautiful UI**: Livewire 3.x components with Tailwind CSS styling
- ⚡ **Performance Optimized**: Built-in caching and MySQL query optimization
- 🔧 **Easy Configuration**: Simple configuration for models and field types
- 📤 **Export Support**: CSV, Excel, and PDF export capabilities
- 🔒 **Security Features**: Permission-based access control
- 📱 **Responsive Design**: Mobile-friendly interface

## Installation

1. Install the package via Composer:

```bash
composer require markmatusek74/reporting-engine
```

2. Publish and run the migrations:

```bash
php artisan vendor:publish --provider="MarkMatusek74\ReportingEngine\ReportingEngineServiceProvider" --tag="reporting-engine-migrations"
php artisan migrate
```

3. Publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="MarkMatusek74\ReportingEngine\ReportingEngineServiceProvider" --tag="reporting-engine-config"
```

4. Publish the views (optional, for customization):

```bash
php artisan vendor:publish --provider="MarkMatusek74\ReportingEngine\ReportingEngineServiceProvider" --tag="reporting-engine-views"
```

## Quick Start

### 1. Register Your Models

Register models that you want to use in reports:

```php
use MarkMatusek74\ReportingEngine\Facades\ReportingEngine;

// In a service provider or controller
ReportingEngine::registerModel(\App\Models\User::class, 'Users');
ReportingEngine::registerModel(\App\Models\Order::class, 'Orders');
```

### 2. Create a Report

Visit `/reporting/reports` in your browser to access the report management interface, or create reports programmatically:

```php
use MarkMatusek74\ReportingEngine\Models\Report;
use MarkMatusek74\ReportingEngine\Models\ReportField;

$report = Report::create([
    'name' => 'User List',
    'description' => 'List of all users with their details',
    'status' => 'active',
    'type' => 'table',
    'is_public' => true,
]);

// Add fields to the report
ReportField::create([
    'report_id' => $report->id,
    'name' => 'user_name',
    'label' => 'Name',
    'type' => 'string',
    'source_table' => 'users',
    'source_column' => 'name',
    'is_sortable' => true,
    'is_searchable' => true,
    'is_filterable' => true,
    'sort_order' => 1,
]);
```

### 3. Display Reports

Use the Livewire components in your views:

```blade
<!-- Report Manager (list all reports) -->
<livewire:report-manager />

<!-- Report Viewer (display specific report) -->
<livewire:report-viewer :report="$report" />

<!-- Report Builder (create/edit reports) -->
<livewire:report-builder :report="$report" />
```

## Configuration

The package configuration file allows you to customize various aspects:

```php
return [
    'database' => [
        'connection' => env('REPORTING_ENGINE_DB_CONNECTION', null),
        'tables' => [
            'reports' => 'reports',
            'report_fields' => 'report_fields',
            'report_models' => 'report_models',
            'report_data' => 'report_data',
        ],
    ],
    
    'reports' => [
        'default_per_page' => 25,
        'max_per_page' => 1000,
        'cache_ttl' => 3600,
        'allowed_field_types' => [
            'string', 'integer', 'decimal', 'boolean', 
            'date', 'datetime', 'text', 'json',
        ],
        'export_formats' => ['csv', 'excel', 'pdf'],
    ],
    
    'security' => [
        'middleware' => ['web', 'auth'],
        'permissions' => [
            'view_reports' => 'view-reports',
            'create_reports' => 'create-reports',
            'edit_reports' => 'edit-reports',
            'delete_reports' => 'delete-reports',
        ],
    ],
];
```

## Database Schema

The package creates four main tables:

### Reports Table
- `id` - Primary key
- `name` - Report name
- `slug` - URL-friendly identifier
- `description` - Report description
- `status` - active/inactive/draft
- `type` - table/chart/dashboard
- `configuration` - JSON configuration data
- `filters` - Default filters (JSON)
- `sorting` - Default sorting (JSON)
- `is_public` - Public accessibility
- `is_cached` - Enable caching
- `cache_ttl` - Cache time-to-live

### Report Fields Table
- `id` - Primary key
- `report_id` - Foreign key to reports
- `name` - Field identifier
- `label` - Display label
- `type` - Field type (string, integer, etc.)
- `source_table` - Database table
- `source_column` - Database column
- `is_sortable` - Can be sorted
- `is_searchable` - Can be searched
- `is_filterable` - Can be filtered
- `is_visible` - Visible in output
- `sort_order` - Display order

### Report Models Table
- `id` - Primary key
- `name` - Human-readable name
- `model_class` - Laravel model class
- `table_name` - Database table
- `available_fields` - JSON of available fields
- `relationships` - JSON of model relationships

### Report Data Table (Caching)
- `id` - Primary key
- `report_id` - Foreign key to reports
- `data` - Cached report data (JSON)
- `filters_applied` - Applied filters (JSON)
- `generated_at` - Cache generation time
- `expires_at` - Cache expiration time

## Advanced Usage

### Custom Field Types

You can add custom field types by extending the configuration:

```php
// In a service provider
$this->app['config']->set('reporting-engine.reports.allowed_field_types', [
    'string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 
    'text', 'json', 'custom_type'
]);
```

### Custom Filters

Create custom filters by extending the ReportField model:

```php
class CustomReportField extends ReportField 
{
    public function applyCustomFilter($query, $value)
    {
        // Custom filter logic
        return $query->where('custom_condition', $value);
    }
}
```

### Relationship Fields

Define fields that span model relationships:

```php
ReportField::create([
    'report_id' => $report->id,
    'name' => 'user_role',
    'label' => 'User Role',
    'type' => 'string',
    'source_table' => 'roles',
    'source_column' => 'name',
    'relationship' => 'belongsTo',
    'related_model' => 'App\Models\Role',
    'related_field' => 'role_id',
]);
```

## API Usage

Generate report data programmatically:

```php
use MarkMatusek74\ReportingEngine\Services\ReportingEngineService;

$service = app(ReportingEngineService::class);

// Generate report with filters
$data = $service->generateReport($report, [
    'status' => 'active',
    'created_at' => [
        'operator' => 'between',
        'value' => ['2024-01-01', '2024-12-31']
    ]
], [
    'per_page' => 50,
    'page' => 1
]);

// Register models programmatically
$reportModel = $service->registerModel(\App\Models\Product::class, 'Products');
```

## Customization

### Views

Publish and customize the views:

```bash
php artisan vendor:publish --provider="MarkMatusek74\ReportingEngine\ReportingEngineServiceProvider" --tag="reporting-engine-views"
```

### Styling

The package uses Tailwind CSS by default. You can:

1. Customize the Tailwind configuration
2. Override specific component styles
3. Use your own CSS framework by disabling Tailwind in the config

## Performance Optimization

### Caching

Enable caching for better performance:

```php
$report->update([
    'is_cached' => true,
    'cache_ttl' => 3600, // 1 hour
]);
```

### Database Indexes

The package automatically creates indexes for commonly queried fields. For optimal performance, ensure your source tables have appropriate indexes.

### Query Optimization

The reporting engine optimizes queries by:
- Using proper joins for relationships
- Limiting data with pagination
- Caching frequently accessed reports
- Using database-level sorting and filtering

## Security

### Middleware

Configure middleware for report access:

```php
'security' => [
    'middleware' => ['web', 'auth', 'verified'],
],
```

### Permissions

Implement permission-based access:

```php
'permissions' => [
    'view_reports' => 'reports.view',
    'create_reports' => 'reports.create',
    'edit_reports' => 'reports.edit',
    'delete_reports' => 'reports.delete',
],
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

For support, please open an issue on the GitHub repository or contact the maintainer.