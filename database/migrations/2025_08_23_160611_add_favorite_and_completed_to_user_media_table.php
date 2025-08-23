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
        Schema::table('user_media', function (Blueprint $table) {
            // is_favorite is already in create_user_media_table, so we remove it here.
            $table->boolean('is_completed')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_media', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};