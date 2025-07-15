<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Migrator;

// Get command line arguments
$command = $argv[1] ?? 'help';
$option = $argv[2] ?? null;

try {
    $migrator = new Migrator();
    
    switch ($command) {
        case 'migrate':
            echo "Running migrations...\n";
            $migrator->migrate();
            echo "\nMigration completed!\n";
            break;
            
        case 'rollback':
            $steps = is_numeric($option) ? (int)$option : 1;
            echo "Rolling back {$steps} migration(s)...\n";
            $migrator->rollback($steps);
            echo "\nRollback completed!\n";
            break;
            
        case 'status':
            $migrator->status();
            break;
            
        case 'make':
            if (!$option) {
                echo "ERROR: Please provide migration name\n";
                echo "Usage: php migrate.php make create_products_table\n";
                exit(1);
            }
            makeMigration($option);
            break;
            
        case 'fresh':
            echo "Dropping all tables and re-running migrations...\n";
            // This would drop all tables and re-migrate (implement if needed)
            echo "WARNING: Fresh migrations not implemented yet\n";
            break;
            
        default:
            showHelp();
            break;
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

function makeMigration($name) {
    $timestamp = date('Y_m_d_His');
    $filename = "{$timestamp}_{$name}";
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    
    // Create migrations directory if it doesn't exist
    $migrationsDir = __DIR__ . '/database/migrations';
    if (!is_dir($migrationsDir)) {
        mkdir($migrationsDir, 0755, true);
    }
    
    $template = "<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\\Core\\Migration;

class {$className} extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        \$this->createTable('example_table', function(\$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        \$this->dropTable('example_table');
    }
}
";
    
    $filepath = "{$migrationsDir}/{$filename}.php";
    file_put_contents($filepath, $template);
    
    echo "Migration created: {$filename}.php\n";
    echo "Edit the file to define your table structure\n";
}

function showHelp() {
    echo "Laravel-style Migration System\n";
    echo "==============================\n\n";
    echo "Available commands:\n";
    echo "  migrate              Run pending migrations\n";
    echo "  rollback [steps]     Rollback migrations (default: 1)\n";
    echo "  status               Show migration status\n";
    echo "  make <name>          Create new migration\n";
    echo "  fresh                Drop all tables and re-migrate\n";
    echo "  help                 Show this help\n\n";
    echo "Examples:\n";
    echo "  php migrate.php migrate\n";
    echo "  php migrate.php make create_products_table\n";
    echo "  php migrate.php rollback 3\n";
    echo "  php migrate.php status\n";
}
