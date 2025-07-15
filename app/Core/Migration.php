<?php

namespace App\Core;

use App\Core\Database;

abstract class Migration
{
    protected $db;
    protected $connection;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Run the migration
     */
    abstract public function up();

    /**
     * Reverse the migration
     */
    abstract public function down();

    /**
     * Execute raw SQL
     */
    protected function execute($sql)
    {
        return $this->connection->exec($sql);
    }

    /**
     * Create table helper
     */
    protected function createTable($tableName, $callback)
    {
        $schema = new Schema($this->connection);
        $table = new Table($tableName);
        $callback($table);
        $schema->create($table);
    }

    /**
     * Drop table helper
     */
    protected function dropTable($tableName)
    {
        $this->execute("DROP TABLE IF EXISTS `{$tableName}`");
    }

    /**
     * Add column helper
     */
    protected function addColumn($tableName, $columnName, $type, $options = [])
    {
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$type}";
        
        if (isset($options['nullable']) && !$options['nullable']) {
            $sql .= " NOT NULL";
        }
        
        if (isset($options['default'])) {
            $sql .= " DEFAULT '{$options['default']}'";
        }
        
        $this->execute($sql);
    }

    /**
     * Drop column helper
     */
    protected function dropColumn($tableName, $columnName)
    {
        $this->execute("ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`");
    }
}
