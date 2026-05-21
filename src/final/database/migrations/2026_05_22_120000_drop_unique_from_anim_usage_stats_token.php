<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anim_usage_stats', function (Blueprint $table) {
            $table->dropUnique('anim_usage_stats_token_unique');
            $table->index(['token', 'anim']);
        });
    }

    public function down(): void
    {
        Schema::table('anim_usage_stats', function (Blueprint $table) {
            $table->dropIndex(['token', 'anim']);
            $table->unique('token');
        });
    }
};
