---
layout: default
title: Database
nav_order: 7
---

# Database
{: .no_toc }

Complete database integration with query builder, migrations, and Eloquent-style models.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

The database system provides:
- PDO-based connection with multiple database support
- Eloquent-style model relationships
- Query builder for complex queries
- Database migrations system
- Connection pooling and caching

---

## Configuration

### Database Config

The database configuration is located in `config/database.php`:

```php
<?php

return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'tool_site_db',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => $_ENV['DB_DATABASE'] ?? 'database/database.sqlite',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ],
        
        'postgresql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_DATABASE'] ?? 'tool_site_db',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ]
    ]
];
```

### Environment Variables

Create a `.env` file in your project root:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tool_site_db
DB_USERNAME=root
DB_PASSWORD=

# Alternative for development
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

---

## Database Class

### Core Database Connection

```php
<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection = null;
    private $config;

    private function __construct()
    {
        $this->config = require 'config/database.php';
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        $connectionName = $this->config['default'];
        $config = $this->config['connections'][$connectionName];
        
        try {
            $dsn = $this->buildDsn($config);
            $this->connection = new PDO(
                $dsn,
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['options'] ?? []
            );
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function buildDsn($config)
    {
        switch ($config['driver']) {
            case 'mysql':
                return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            case 'sqlite':
                return "sqlite:{$config['database']}";
            
            case 'pgsql':
                return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            
            default:
                throw new \Exception("Unsupported database driver: {$config['driver']}");
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    public function update($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollback();
    }
}
```

---

## Query Builder

### Basic Query Builder

```php
<?php

namespace App\Core;

class QueryBuilder
{
    private $db;
    private $table;
    private $select = ['*'];
    private $joins = [];
    private $where = [];
    private $orderBy = [];
    private $groupBy = [];
    private $having = [];
    private $limit = null;
    private $offset = null;
    private $params = [];

    public function __construct($table = null)
    {
        $this->db = Database::getInstance();
        $this->table = $table;
    }

    public static function table($table)
    {
        return new self($table);
    }

    public function select($columns = ['*'])
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER')
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    public function leftJoin($table, $first, $operator, $second)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin($table, $first, $operator, $second)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function whereIn($column, $values)
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $this->where[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => 'IN',
            'value' => "($placeholders)",
            'params' => $values
        ];

        return $this;
    }

    public function whereBetween($column, $min, $max)
    {
        $this->where[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => 'BETWEEN',
            'value' => '? AND ?',
            'params' => [$min, $max]
        ];

        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function groupBy($columns)
    {
        $this->groupBy = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having($column, $operator, $value)
    {
        $this->having[] = "$column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $sql = $this->buildSelectQuery();
        $params = $this->buildParams();
        return $this->db->fetchAll($sql, $params);
    }

    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        return $result ? $result[0] : null;
    }

    public function count()
    {
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as count'];
        
        $sql = $this->buildSelectQuery();
        $params = $this->buildParams();
        $result = $this->db->fetchOne($sql, $params);
        
        $this->select = $originalSelect;
        return $result ? $result['count'] : 0;
    }

    public function insert(array $data)
    {
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";
        
        return $this->db->insert($sql, array_values($data));
    }

    public function update(array $data)
    {
        $sets = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($params);
        }
        
        return $this->db->update($sql, $params);
    }

    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";
        $params = [];
        
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($params);
        }
        
        return $this->db->delete($sql, $params);
    }

    private function buildSelectQuery()
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . " FROM {$this->table}";
        
        // Add JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Add WHERE clause
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        // Add GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        // Add HAVING
        if (!empty($this->having)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->having);
        }
        
        // Add ORDER BY
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        // Add LIMIT and OFFSET
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }
        
        return $sql;
    }

    private function buildWhereClause(&$params = null)
    {
        if ($params === null) {
            $params = [];
        }

        $conditions = [];
        $first = true;
        
        foreach ($this->where as $condition) {
            $type = $first ? '' : $condition['type'];
            $first = false;
            
            if ($condition['operator'] === 'IN' || $condition['operator'] === 'BETWEEN') {
                $conditions[] = "$type {$condition['column']} {$condition['operator']} {$condition['value']}";
                if (isset($condition['params'])) {
                    $params = array_merge($params, $condition['params']);
                }
            } else {
                $conditions[] = "$type {$condition['column']} {$condition['operator']} ?";
                $params[] = $condition['value'];
            }
        }
        
        return implode(' ', $conditions);
    }

    private function buildParams()
    {
        $params = [];
        
        foreach ($this->where as $condition) {
            if (isset($condition['params'])) {
                $params = array_merge($params, $condition['params']);
            } else {
                $params[] = $condition['value'];
            }
        }
        
        return array_merge($params, $this->params);
    }
}
```

---

## Migrations

### Migration System

```php
<?php

namespace App\Core;

class Migration
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createTable($table, callable $callback)
    {
        $schema = new Schema($table);
        $callback($schema);
        
        $sql = $schema->build();
        $this->db->query($sql);
    }

    public function dropTable($table)
    {
        $this->db->query("DROP TABLE IF EXISTS $table");
    }

    public function addColumn($table, $column, $type, $options = [])
    {
        $sql = "ALTER TABLE $table ADD COLUMN $column $type";
        
        if (isset($options['null']) && !$options['null']) {
            $sql .= ' NOT NULL';
        }
        
        if (isset($options['default'])) {
            $sql .= " DEFAULT '{$options['default']}'";
        }
        
        $this->db->query($sql);
    }

    public function dropColumn($table, $column)
    {
        $this->db->query("ALTER TABLE $table DROP COLUMN $column");
    }

    public function addIndex($table, $columns, $name = null)
    {
        $columns = is_array($columns) ? implode(',', $columns) : $columns;
        $name = $name ?: "idx_{$table}_" . str_replace(',', '_', $columns);
        
        $this->db->query("CREATE INDEX $name ON $table ($columns)");
    }

    public function dropIndex($table, $name)
    {
        $this->db->query("DROP INDEX $name ON $table");
    }
}

class Schema
{
    private $table;
    private $columns = [];
    private $indexes = [];
    private $foreignKeys = [];

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function id($name = 'id')
    {
        $this->columns[] = "$name INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string($name, $length = 255)
    {
        $this->columns[] = "$name VARCHAR($length)";
        return $this;
    }

    public function text($name)
    {
        $this->columns[] = "$name TEXT";
        return $this;
    }

    public function integer($name)
    {
        $this->columns[] = "$name INT";
        return $this;
    }

    public function boolean($name)
    {
        $this->columns[] = "$name BOOLEAN DEFAULT FALSE";
        return $this;
    }

    public function timestamp($name)
    {
        $this->columns[] = "$name TIMESTAMP";
        return $this;
    }

    public function timestamps()
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function index($columns, $name = null)
    {
        $columns = is_array($columns) ? implode(',', $columns) : $columns;
        $name = $name ?: "idx_{$this->table}_" . str_replace(',', '_', $columns);
        $this->indexes[] = "INDEX $name ($columns)";
        return $this;
    }

    public function foreign($column, $references, $on = null)
    {
        $constraint = "FOREIGN KEY ($column) REFERENCES $references";
        if ($on) {
            $constraint .= " ON DELETE $on ON UPDATE $on";
        }
        $this->foreignKeys[] = $constraint;
        return $this;
    }

    public function build()
    {
        $sql = "CREATE TABLE {$this->table} (\n";
        
        $parts = array_merge($this->columns, $this->indexes, $this->foreignKeys);
        $sql .= "    " . implode(",\n    ", $parts);
        
        $sql .= "\n)";
        
        return $sql;
    }
}
```

### Creating Migrations

```php
<?php
// database/migrations/001_create_users_table.php

use App\Core\Migration;

return new class extends Migration {
    public function up()
    {
        $this->createTable('users', function($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            
            $table->index('email');
        });
    }
    
    public function down()
    {
        $this->dropTable('users');
    }
};
```

### Running Migrations

```php
<?php
// migrate.php

require 'vendor/autoload.php';

class MigrationRunner
{
    private $migrationPath = 'database/migrations/';
    
    public function run()
    {
        $files = glob($this->migrationPath . '*.php');
        sort($files);
        
        foreach ($files as $file) {
            echo "Running migration: " . basename($file) . "\n";
            
            $migration = require $file;
            $migration->up();
            
            echo "Migration completed: " . basename($file) . "\n";
        }
    }
    
    public function rollback()
    {
        $files = glob($this->migrationPath . '*.php');
        rsort($files); // Reverse order for rollback
        
        foreach ($files as $file) {
            echo "Rolling back migration: " . basename($file) . "\n";
            
            $migration = require $file;
            $migration->down();
            
            echo "Rollback completed: " . basename($file) . "\n";
        }
    }
}

// Usage
$runner = new MigrationRunner();

if ($argc > 1 && $argv[1] === 'rollback') {
    $runner->rollback();
} else {
    $runner->run();
}
```

---

## Relationships

### Model Relationships

```php
<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];

    // One-to-many relationship
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
    
    // Many-to-many relationship
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
    
    // One-to-one relationship
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }
}

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id'];

    // Belongs to relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Many-to-many relationship
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    }
}
```

### Relationship Implementation in Model

```php
<?php

namespace App\Core;

abstract class Model
{
    // ... existing code ...

    public function hasMany($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: strtolower(class_basename($this)) . '_id';
        
        return new HasMany($this, new $related, $foreignKey, $localKey);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = 'id')
    {
        $foreignKey = $foreignKey ?: strtolower(class_basename($related)) . '_id';
        
        return new BelongsTo($this, new $related, $foreignKey, $ownerKey);
    }

    public function hasOne($related, $foreignKey = null, $localKey = 'id')
    {
        $foreignKey = $foreignKey ?: strtolower(class_basename($this)) . '_id';
        
        return new HasOne($this, new $related, $foreignKey, $localKey);
    }

    public function belongsToMany($related, $table = null, $foreignKey = null, $relatedKey = null)
    {
        $table = $table ?: $this->getPivotTableName($related);
        $foreignKey = $foreignKey ?: strtolower(class_basename($this)) . '_id';
        $relatedKey = $relatedKey ?: strtolower(class_basename($related)) . '_id';
        
        return new BelongsToMany($this, new $related, $table, $foreignKey, $relatedKey);
    }

    private function getPivotTableName($related)
    {
        $models = [
            strtolower(class_basename($this)),
            strtolower(class_basename($related))
        ];
        sort($models);
        return implode('_', $models);
    }
}
```

### Relationship Classes

```php
<?php

namespace App\Core\Relations;

class HasMany
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $localKey;

    public function __construct($parent, $related, $foreignKey, $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function get()
    {
        return $this->related->where($this->foreignKey, $this->parent->{$this->localKey})->get();
    }

    public function create(array $attributes)
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};
        return $this->related->create($attributes);
    }
}

class BelongsTo
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $ownerKey;

    public function __construct($parent, $related, $foreignKey, $ownerKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    public function get()
    {
        return $this->related->where($this->ownerKey, $this->parent->{$this->foreignKey})->first();
    }
}
```

---

## Database Testing

### Test Database Setup

```php
<?php

namespace Tests;

use App\Core\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        // Use in-memory SQLite for testing
        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ];
        
        $db = Database::getInstance();
        $db->connect($config);
        
        $this->createTestTables();
    }

    protected function createTestTables()
    {
        $db = Database::getInstance();
        
        $db->query("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255),
                email VARCHAR(255) UNIQUE,
                password VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function tearDown(): void
    {
        // Database is automatically cleaned up for in-memory SQLite
    }
}
```

### Model Testing

```php
<?php

namespace Tests\Models;

use App\Models\User;
use Tests\DatabaseTestCase;

class UserTest extends DatabaseTestCase
{
    public function testCanCreateUser()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function testCanFindUser()
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123'
        ]);

        $found = User::find($user->id);
        $this->assertEquals($user->id, $found->id);
        $this->assertEquals('Jane Doe', $found->name);
    }

    public function testCanUpdateUser()
    {
        $user = User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => 'password123'
        ]);

        $user->update(['name' => 'Robert Smith']);
        
        $updated = User::find($user->id);
        $this->assertEquals('Robert Smith', $updated->name);
    }

    public function testCanDeleteUser()
    {
        $user = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => 'password123'
        ]);

        $user->delete();
        
        $found = User::find($user->id);
        $this->assertNull($found);
    }
}
```

This comprehensive database system provides everything needed for modern PHP applications with clean, testable code and powerful query capabilities.
