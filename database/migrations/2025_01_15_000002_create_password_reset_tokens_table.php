<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Migration;

class CreatePasswordResetTokensTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->createTable('password_reset_tokens', function($table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->dropTable('password_reset_tokens');
    }
}
