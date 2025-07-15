<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->createTable('sessions', function($table) {
            $table->string('id', 40)->primary();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->dropTable('sessions');
    }
}
