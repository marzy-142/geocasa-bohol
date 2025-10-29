<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        // Drop existing foreign key if present, then alter column to nullable, then re-add FK
        Schema::table('messages', function (Blueprint $table) {
            try {
                $table->dropForeign(['sender_id']);
            } catch (\Throwable $e) {
                // FK might not exist yet; ignore
            }
        });

        // Make column nullable without requiring doctrine/dbal
        // MySQL supports MODIFY, SQLite doesn't need it (already nullable by default)
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE messages MODIFY sender_id BIGINT UNSIGNED NULL');
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        // Drop FK, make column NOT NULL again, then re-add FK
        Schema::table('messages', function (Blueprint $table) {
            try {
                $table->dropForeign(['sender_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE messages MODIFY sender_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
