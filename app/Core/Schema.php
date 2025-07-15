<?php

namespace App\Core;

class Schema
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create table from Table object
     */
    public function create(Table $table)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$table->getName()}` (";
        
        $columnDefinitions = [];
        foreach ($table->getColumns() as $name => $definition) {
            $columnDefinitions[] = "`{$name}` {$definition}";
        }
        
        // Add indexes and constraints
        $indexes = $table->getIndexes();
        $allDefinitions = array_merge($columnDefinitions, $indexes);
        
        $sql .= implode(', ', $allDefinitions);
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->connection->exec($sql);
    }

    /**
     * Drop table
     */
    public function drop($tableName)
    {
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        return $this->connection->exec($sql);
    }

    /**
     * Check if table exists
     */
    public function hasTable($tableName)
    {
        $sql = "SHOW TABLES LIKE '{$tableName}'";
        $result = $this->connection->query($sql);
        return $result->rowCount() > 0;
    }

    /**
     * Check if column exists
     */
    public function hasColumn($tableName, $columnName)
    {
        $sql = "SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'";
        $result = $this->connection->query($sql);
        return $result->rowCount() > 0;
    }
}
