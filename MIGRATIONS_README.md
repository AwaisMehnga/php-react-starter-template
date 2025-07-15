# Laravel-Style Migration System

A complete migration system with minimal files that provides Laravel-like functionality.

## 🗂️ Files Created

**Core System (4 files):**
- `app/Core/Migration.php` - Base migration class
- `app/Core/Migrator.php` - Migration runner
- `app/Core/Table.php` - Table schema builder
- `app/Core/Schema.php` - Database schema helper

**Command Interface:**
- `migrate.php` - CLI migration commands

## 🚀 Available Commands

```bash
# Run pending migrations
php migrate.php migrate

# Create new migration
php migrate.php make create_products_table

# Check migration status
php migrate.php status

# Rollback migrations
php migrate.php rollback 1

# Show help
php migrate.php help
```

## 📝 Creating Migrations

### 1. Generate Migration File
```bash
php migrate.php make create_products_table
```

### 2. Edit Migration File
```php
<?php

use App\Core\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->createTable('products', function($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->dropTable('products');
    }
}
```

## 🛠️ Table Builder Methods

### Column Types
```php
$table->id();                          // Auto-increment primary key
$table->string('name', 255);           // VARCHAR
$table->text('description');           // TEXT
$table->integer('count');              // INT
$table->bigInteger('user_id');         // BIGINT
$table->decimal('price', 10, 2);       // DECIMAL
$table->boolean('is_active');          // BOOLEAN
$table->timestamp('created_at');       // TIMESTAMP
$table->timestamps();                  // created_at & updated_at
```

### Column Modifiers
```php
$table->string('email')->nullable();   // Allow NULL
$table->string('name')->notNullable(); // NOT NULL
$table->integer('count')->default(0);  // Default value
$table->string('email')->unique();     // Unique constraint
$table->integer('user_id')->index();   // Index
$table->string('email')->primary();    // Primary key
```

### Advanced Features
```php
// Foreign key (auto-detects table from column name)
$table->integer('user_id')->foreign('id');

// Custom foreign key
$table->integer('author_id')->foreign('id', 'users');

// Multiple column index
$table->index(['user_id', 'status']);
```

## 🔄 Migration Flow

1. **Create:** `php migrate.php make migration_name`
2. **Edit:** Define table structure in generated file
3. **Migrate:** `php migrate.php migrate`
4. **Check:** `php migrate.php status`
5. **Rollback:** `php migrate.php rollback` (if needed)

## 📊 Migration Tracking

Migrations are tracked in the `migrations` table:
- Timestamp-based ordering
- Batch tracking for rollbacks
- Execution history

## 🎯 Benefits

✅ **Laravel-compatible syntax**
✅ **Rollback support**
✅ **Migration status tracking**
✅ **Batch operations**
✅ **Minimal file overhead (4 core files)**
✅ **Type-safe table definitions**
✅ **Automatic timestamp handling**

## 🔧 File Cleanup

You can now safely delete these old files:
- `create-database.php`
- `setup-auth.php`
- `create-admin.php`
- `database/migrations/001_create_users_table.sql`

The new system is fully self-contained and production-ready!
