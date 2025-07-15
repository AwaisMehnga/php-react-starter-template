<?php

namespace App\Core;

use App\Core\Database;

class Migrator
{
    private $db;
    private $connection;
    private $migrationsPath;

    public function __construct($migrationsPath = null)
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
        $this->migrationsPath = $migrationsPath ?: __DIR__ . '/../../database/migrations';
        
        $this->createMigrationsTable();
    }

    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->connection->exec($sql);
    }

    /**
     * Run pending migrations
     */
    public function migrate()
    {
        $pendingMigrations = $this->getPendingMigrations();
        
        if (empty($pendingMigrations)) {
            echo "Nothing to migrate.\n";
            return;
        }

        $batch = $this->getNextBatchNumber();
        
        foreach ($pendingMigrations as $migration) {
            echo "Migrating: {$migration}\n";
            
            try {
                $this->runMigration($migration, 'up');
                $this->recordMigration($migration, $batch);
                echo "Migrated: {$migration}\n";
            } catch (\Exception $e) {
                echo "Migration failed: {$migration}\n";
                echo "Error: " . $e->getMessage() . "\n";
                break;
            }
        }
    }

    /**
     * Rollback migrations
     */
    public function rollback($steps = 1)
    {
        $migrations = $this->getLastBatchMigrations($steps);
        
        if (empty($migrations)) {
            echo "Nothing to rollback.\n";
            return;
        }

        foreach (array_reverse($migrations) as $migration) {
            echo "Rolling back: {$migration}\n";
            
            try {
                $this->runMigration($migration, 'down');
                $this->removeMigrationRecord($migration);
                echo "Rolled back: {$migration}\n";
            } catch (\Exception $e) {
                echo "Rollback failed: {$migration}\n";
                echo "Error: " . $e->getMessage() . "\n";
                break;
            }
        }
    }

    /**
     * Get migration status
     */
    public function status()
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();

        echo "Migration Status:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-50s %s\n", "Migration", "Status");
        echo str_repeat("-", 80) . "\n";

        foreach ($allMigrations as $migration) {
            $status = in_array($migration, $executedMigrations) ? "Migrated" : "Pending";
            printf("%-50s %s\n", $migration, $status);
        }
    }

    /**
     * Get all migration files
     */
    private function getAllMigrationFiles()
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Get pending migrations
     */
    private function getPendingMigrations()
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Get executed migrations
     */
    private function getExecutedMigrations()
    {
        $stmt = $this->connection->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get last batch migrations
     */
    private function getLastBatchMigrations($batches = 1)
    {
        $stmt = $this->connection->prepare("
            SELECT migration FROM migrations 
            WHERE batch > (SELECT COALESCE(MAX(batch), 0) - ? FROM migrations)
            ORDER BY batch DESC, id DESC
        ");
        $stmt->execute([$batches]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Run a migration
     */
    private function runMigration($migrationName, $direction)
    {
        $file = $this->migrationsPath . '/' . $migrationName . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("Migration file not found: {$file}");
        }

        require_once $file;
        
        $className = $this->getClassNameFromFile($migrationName);
        
        if (!class_exists($className)) {
            throw new \Exception("Migration class not found: {$className}");
        }

        $migration = new $className();
        
        if ($direction === 'up') {
            $migration->up();
        } else {
            $migration->down();
        }
    }

    /**
     * Convert filename to class name
     */
    private function getClassNameFromFile($filename)
    {
        // Convert "2025_01_15_000001_create_users_table" to "CreateUsersTable"
        $parts = explode('_', $filename);
        $nameParts = array_slice($parts, 4); // Skip timestamp parts
        
        return str_replace(' ', '', ucwords(str_replace('_', ' ', implode('_', $nameParts))));
    }

    /**
     * Record executed migration
     */
    private function recordMigration($migration, $batch)
    {
        $stmt = $this->connection->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Remove migration record
     */
    private function removeMigrationRecord($migration)
    {
        $stmt = $this->connection->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
    }

    /**
     * Get next batch number
     */
    private function getNextBatchNumber()
    {
        $stmt = $this->connection->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['next_batch'];
    }
}
