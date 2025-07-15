<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->createTable('products', function($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->integer('category_id')->nullable()->index();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->dropTable('products');
    }
}
