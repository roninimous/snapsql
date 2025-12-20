<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('databases', function (Blueprint $table) {
            if (! Schema::hasColumn('databases', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });

        // Set initial sort_order based on creation order for databases that don't have it set
        $databases = \DB::table('databases')->whereNull('sort_order')->orWhere('sort_order', 0)->orderBy('id')->get();
        $maxSortOrder = \DB::table('databases')->max('sort_order') ?? 0;
        foreach ($databases as $index => $db) {
            \DB::table('databases')->where('id', $db->id)->update(['sort_order' => $maxSortOrder + $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
