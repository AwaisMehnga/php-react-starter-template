<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->createTable('users', function($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->dropTable('users');
    }
}
