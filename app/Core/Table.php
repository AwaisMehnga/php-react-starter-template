<?php

namespace App\Core;

class Table
{
    private $name;
    private $columns = [];
    private $indexes = [];
    private $primaryKey = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Auto-incrementing primary key
     */
    public function id($name = 'id')
    {
        $this->primaryKey = $name;
        $this->columns[$name] = "BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    /**
     * Set primary key
     */
    public function primary()
    {
        $lastColumn = array_key_last($this->columns);
        if ($lastColumn) {
            $this->primaryKey = $lastColumn;
            $this->columns[$lastColumn] .= " PRIMARY KEY";
        }
        return $this;
    }

    /**
     * String column
     */
    public function string($name, $length = 255)
    {
        $this->columns[$name] = "VARCHAR({$length})";
        return $this;
    }

    /**
     * Text column
     */
    public function text($name)
    {
        $this->columns[$name] = "TEXT";
        return $this;
    }

    /**
     * Integer column
     */
    public function integer($name)
    {
        $this->columns[$name] = "INT";
        return $this;
    }

    /**
     * Big integer column
     */
    public function bigInteger($name)
    {
        $this->columns[$name] = "BIGINT";
        return $this;
    }

    /**
     * Decimal column
     */
    public function decimal($name, $precision = 8, $scale = 2)
    {
        $this->columns[$name] = "DECIMAL({$precision}, {$scale})";
        return $this;
    }

    /**
     * Boolean column
     */
    public function boolean($name)
    {
        $this->columns[$name] = "BOOLEAN DEFAULT FALSE";
        return $this;
    }

    /**
     * Timestamp column
     */
    public function timestamp($name)
    {
        $this->columns[$name] = "TIMESTAMP NULL";
        return $this;
    }

    /**
     * Email column (string with unique constraint)
     */
    public function email($name = 'email')
    {
        $this->string($name)->unique();
        return $this;
    }

    /**
     * Created at and updated at timestamps
     */
    public function timestamps()
    {
        $this->columns['created_at'] = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns['updated_at'] = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    /**
     * Make column nullable
     */
    public function nullable()
    {
        $lastColumn = array_key_last($this->columns);
        if ($lastColumn) {
            $this->columns[$lastColumn] = str_replace(' NOT NULL', '', $this->columns[$lastColumn]);
            if (strpos($this->columns[$lastColumn], 'NULL') === false) {
                $this->columns[$lastColumn] .= " NULL";
            }
        }
        return $this;
    }

    /**
     * Make column not nullable
     */
    public function notNullable()
    {
        $lastColumn = array_key_last($this->columns);
        if ($lastColumn) {
            $this->columns[$lastColumn] = str_replace(' NULL', '', $this->columns[$lastColumn]);
            $this->columns[$lastColumn] .= " NOT NULL";
        }
        return $this;
    }

    /**
     * Add default value
     */
    public function default($value)
    {
        $lastColumn = array_key_last($this->columns);
        if ($lastColumn) {
            if (is_string($value) && $value !== 'CURRENT_TIMESTAMP') {
                $this->columns[$lastColumn] .= " DEFAULT '{$value}'";
            } else {
                $this->columns[$lastColumn] .= " DEFAULT {$value}";
            }
        }
        return $this;
    }

    /**
     * Add unique constraint
     */
    public function unique()
    {
        $lastColumn = array_key_last($this->columns);
        if ($lastColumn) {
            $this->indexes[] = "UNIQUE KEY `unique_{$lastColumn}` (`{$lastColumn}`)";
        }
        return $this;
    }

    /**
     * Add index
     */
    public function index($columns = null)
    {
        if ($columns === null) {
            $lastColumn = array_key_last($this->columns);
            $columns = [$lastColumn];
        }
        
        if (is_string($columns)) {
            $columns = [$columns];
        }
        
        $indexName = 'idx_' . implode('_', $columns);
        $columnList = '`' . implode('`, `', $columns) . '`';
        $this->indexes[] = "INDEX `{$indexName}` ({$columnList})";
        return $this;
    }

    /**
     * Foreign key constraint
     */
    public function foreign($column, $references, $on = null)
    {
        if ($on === null) {
            // Assume table name from column (e.g., user_id -> users table)
            $on = str_replace('_id', 's', $column);
        }
        
        $constraintName = "fk_{$this->name}_{$column}";
        $this->indexes[] = "CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `{$on}` (`{$references}`)";
        return $this;
    }
}
