<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE backup_destinations MODIFY COLUMN type ENUM('local', 's3', 'ftp', 'sftp', 'b2', 'gdrive') DEFAULT 'local'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE backup_destinations MODIFY COLUMN type ENUM('local', 's3', 'ftp', 'sftp') DEFAULT 'local'");
    }
};
