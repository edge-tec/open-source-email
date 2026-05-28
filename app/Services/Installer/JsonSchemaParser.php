<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class JsonSchemaParser
{
    protected string $schemaPath;
    protected array $parsedSchemas = [];

    public function __construct(?string $schemaPath = null)
    {
        $this->schemaPath = $schemaPath ?? database_path('json');
    }

    /**
     * Load and parse all JSON schema files from the schema directory.
     */
    public function loadAll(): array
    {
        $files = File::glob($this->schemaPath . '/*.json');
        $schemas = [];

        foreach ($files as $file) {
            $schema = $this->parseFile($file);
            $schemas[$schema['table']] = $schema;
        }

        // Sort by dependency order (tables with no foreign keys first)
        return $this->sortByDependency($schemas);
    }

    /**
     * Parse a single JSON schema file.
     */
    public function parseFile(string $filePath): array
    {
        if (!File::exists($filePath)) {
            throw new InvalidArgumentException("Schema file not found: {$filePath}");
        }

        $content = File::get($filePath);
        $schema = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                "Invalid JSON in {$filePath}: " . json_last_error_msg()
            );
        }

        $this->validateSchema($schema, $filePath);
        $this->parsedSchemas[$schema['table']] = $schema;

        return $schema;
    }

    /**
     * Validate a schema has required fields.
     */
    protected function validateSchema(array $schema, string $filePath): void
    {
        if (!isset($schema['table'])) {
            throw new InvalidArgumentException("Missing 'table' key in {$filePath}");
        }

        if (!isset($schema['columns']) || !is_array($schema['columns'])) {
            throw new InvalidArgumentException("Missing or invalid 'columns' in {$filePath}");
        }

        foreach ($schema['columns'] as $index => $column) {
            if (!isset($column['name']) || !isset($column['type'])) {
                throw new InvalidArgumentException(
                    "Column at index {$index} missing 'name' or 'type' in {$filePath}"
                );
            }
        }
    }

    /**
     * Convert a column definition to a Laravel migration method call string.
     */
    public function columnToMigration(array $column): string
    {
        $type = $column['type'];
        $name = $column['name'];
        $line = '';

        switch ($type) {
            case 'bigIncrements':
                $line = "\$table->bigIncrements('{$name}')";
                break;
            case 'increments':
                $line = "\$table->increments('{$name}')";
                break;
            case 'bigInteger':
                $line = "\$table->bigInteger('{$name}')";
                if (!empty($column['unsigned'])) {
                    $line .= '->unsigned()';
                }
                break;
            case 'integer':
                $line = "\$table->integer('{$name}')";
                if (!empty($column['unsigned'])) {
                    $line .= '->unsigned()';
                }
                break;
            case 'string':
                $length = $column['length'] ?? 255;
                $line = "\$table->string('{$name}', {$length})";
                break;
            case 'text':
                $line = "\$table->text('{$name}')";
                break;
            case 'longText':
                $line = "\$table->longText('{$name}')";
                break;
            case 'boolean':
                $line = "\$table->boolean('{$name}')";
                break;
            case 'date':
                $line = "\$table->date('{$name}')";
                break;
            case 'timestamp':
                $line = "\$table->timestamp('{$name}')";
                break;
            case 'decimal':
                $precision = $column['precision'] ?? 8;
                $scale = $column['scale'] ?? 2;
                $line = "\$table->decimal('{$name}', {$precision}, {$scale})";
                break;
            case 'enum':
                $allowed = $column['allowed'] ?? [];
                $allowedStr = "['" . implode("', '", $allowed) . "']";
                $line = "\$table->enum('{$name}', {$allowedStr})";
                break;
            case 'json':
                $line = "\$table->json('{$name}')";
                break;
            default:
                $line = "\$table->{$type}('{$name}')";
        }

        // Modifiers
        if (!empty($column['nullable'])) {
            $line .= '->nullable()';
        }

        if (array_key_exists('default', $column) && $column['default'] !== null) {
            $default = $column['default'];
            if (is_bool($default)) {
                $default = $default ? 'true' : 'false';
                $line .= "->default({$default})";
            } elseif (is_numeric($default)) {
                $line .= "->default({$default})";
            } else {
                $line .= "->default('{$default}')";
            }
        }

        if (!empty($column['unique'])) {
            $line .= '->unique()';
        }

        if (!empty($column['comment'])) {
            $line .= "->comment('{$column['comment']}')";
        }

        return $line . ';';
    }

    /**
     * Generate the complete migration file content for a schema.
     */
    public function generateMigrationContent(array $schema): string
    {
        $table = $schema['table'];
        $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . 'Table';

        $columns = '';
        foreach ($schema['columns'] as $column) {
            $columns .= '            ' . $this->columnToMigration($column) . "\n";
        }

        // Timestamps
        if (!empty($schema['timestamps'])) {
            $columns .= "            \$table->timestamps();\n";
        }

        // Soft deletes
        if (!empty($schema['soft_deletes'])) {
            $columns .= "            \$table->softDeletes();\n";
        }

        // Indexes
        $indexes = '';
        if (!empty($schema['indexes'])) {
            $indexes .= "\n";
            foreach ($schema['indexes'] as $index) {
                $cols = "['" . implode("', '", $index['columns']) . "']";
                $type = $index['type'] ?? 'index';
                $indexes .= "            \$table->{$type}({$cols});\n";
            }
        }

        // Foreign keys
        $foreignKeys = '';
        if (!empty($schema['foreign_keys'])) {
            $foreignKeys .= "\n";
            foreach ($schema['foreign_keys'] as $fk) {
                $foreignKeys .= "            \$table->foreign('{$fk['column']}')"
                    . "->references('{$fk['references']}')"
                    . "->on('{$fk['on']}')";
                if (!empty($fk['onDelete'])) {
                    $foreignKeys .= "->onDelete('{$fk['onDelete']}')";
                }
                if (!empty($fk['onUpdate'])) {
                    $foreignKeys .= "->onUpdate('{$fk['onUpdate']}')";
                }
                $foreignKeys .= ";\n";
            }
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
{$columns}{$indexes}{$foreignKeys}        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }

    /**
     * Sort schemas by foreign key dependencies (topological sort).
     */
    protected function sortByDependency(array $schemas): array
    {
        $sorted = [];
        $visited = [];
        $tableNames = array_keys($schemas);

        foreach ($tableNames as $table) {
            $this->visitForSort($table, $schemas, $sorted, $visited);
        }

        return $sorted;
    }

    protected function visitForSort(string $table, array $schemas, array &$sorted, array &$visited): void
    {
        if (isset($visited[$table])) {
            return;
        }

        $visited[$table] = true;

        if (isset($schemas[$table]['foreign_keys'])) {
            foreach ($schemas[$table]['foreign_keys'] as $fk) {
                $depTable = $fk['on'];
                if ($depTable !== $table && isset($schemas[$depTable])) {
                    $this->visitForSort($depTable, $schemas, $sorted, $visited);
                }
            }
        }

        $sorted[$table] = $schemas[$table];
    }

    /**
     * Get all table names from JSON schemas.
     */
    public function getTableNames(): array
    {
        return array_keys($this->loadAll());
    }
}
