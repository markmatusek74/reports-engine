<?php

namespace MarkMatusek74\ReportingEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MarkMatusek74\ReportingEngine\Database\Factories\ReportModelFactory;

class ReportModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model_class',
        'table_name',
        'primary_key',
        'description',
        'available_fields',
        'relationships',
        'scopes',
        'is_active',
    ];

    protected $casts = [
        'available_fields' => 'array',
        'relationships' => 'array',
        'scopes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the model instance.
     */
    public function getModelInstance()
    {
        if (!class_exists($this->model_class)) {
            throw new \Exception("Model class {$this->model_class} does not exist");
        }

        return new $this->model_class;
    }

    /**
     * Check if model is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get available fields for reporting.
     */
    public function getAvailableFields(): array
    {
        return $this->available_fields ?? [];
    }

    /**
     * Get available relationships.
     */
    public function getRelationships(): array
    {
        return $this->relationships ?? [];
    }

    /**
     * Get available query scopes.
     */
    public function getScopes(): array
    {
        return $this->scopes ?? [];
    }

    /**
     * Check if model has a specific field.
     */
    public function hasField(string $field): bool
    {
        return in_array($field, $this->getAvailableFields());
    }

    /**
     * Check if model has a specific relationship.
     */
    public function hasRelationship(string $relationship): bool
    {
        return array_key_exists($relationship, $this->getRelationships());
    }

    /**
     * Check if model has a specific scope.
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->getScopes());
    }

    /**
     * Get field information.
     */
    public function getFieldInfo(string $field): ?array
    {
        $fields = $this->getAvailableFields();
        return $fields[$field] ?? null;
    }

    /**
     * Get relationship information.
     */
    public function getRelationshipInfo(string $relationship): ?array
    {
        $relationships = $this->getRelationships();
        return $relationships[$relationship] ?? null;
    }

    /**
     * Refresh model information from the actual model class.
     */
    public function refreshModelInfo(): void
    {
        try {
            $instance = $this->getModelInstance();
            
            // Get table information
            $this->table_name = $instance->getTable();
            $this->primary_key = $instance->getKeyName();
            
            // Get fillable fields
            $fillable = $instance->getFillable();
            $guarded = $instance->getGuarded();
            
            // Get all columns from database
            $schema = \Illuminate\Support\Facades\Schema::getColumnListing($this->table_name);
            
            $availableFields = [];
            foreach ($schema as $column) {
                $columnType = \Illuminate\Support\Facades\Schema::getColumnType($this->table_name, $column);
                $availableFields[$column] = [
                    'type' => $this->mapDatabaseType($columnType),
                    'fillable' => in_array($column, $fillable),
                    'guarded' => in_array($column, $guarded),
                ];
            }
            
            $this->available_fields = $availableFields;
            
            // Get relationships using reflection
            $relationships = $this->discoverRelationships($instance);
            $this->relationships = $relationships;
            
            // Get scopes
            $scopes = $this->discoverScopes($instance);
            $this->scopes = $scopes;
            
            $this->save();
            
        } catch (\Exception $e) {
            \Log::error("Failed to refresh model info for {$this->model_class}: " . $e->getMessage());
        }
    }

    /**
     * Map database type to reporting engine type.
     */
    protected function mapDatabaseType(string $dbType): string
    {
        return match ($dbType) {
            'integer', 'bigint', 'smallint', 'tinyint' => 'integer',
            'decimal', 'float', 'double' => 'decimal',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'text', 'longtext', 'mediumtext' => 'text',
            'json' => 'json',
            default => 'string',
        };
    }

    /**
     * Discover relationships in the model.
     */
    protected function discoverRelationships($instance): array
    {
        $relationships = [];
        
        try {
            $reflection = new \ReflectionClass($instance);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            foreach ($methods as $method) {
                if ($method->getNumberOfParameters() === 0 && 
                    $method->class === $reflection->getName()) {
                    
                    try {
                        $result = $method->invoke($instance);
                        
                        if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                            $relationships[$method->getName()] = [
                                'type' => class_basename($result),
                                'related_model' => get_class($result->getRelated()),
                            ];
                        }
                    } catch (\Exception $e) {
                        // Skip if method throws exception
                    }
                }
            }
        } catch (\Exception $e) {
            // Skip if reflection fails
        }
        
        return $relationships;
    }

    /**
     * Discover query scopes in the model.
     */
    protected function discoverScopes($instance): array
    {
        $scopes = [];
        
        try {
            $reflection = new \ReflectionClass($instance);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if (str_starts_with($methodName, 'scope') && 
                    $method->class === $reflection->getName()) {
                    
                    $scopeName = lcfirst(substr($methodName, 5));
                    $scopes[] = $scopeName;
                }
            }
        } catch (\Exception $e) {
            // Skip if reflection fails
        }
        
        return $scopes;
    }

    /**
     * Scope for active models.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ReportModelFactory::new();
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return config('reporting-engine.database.tables.report_models', parent::getTable());
    }
}