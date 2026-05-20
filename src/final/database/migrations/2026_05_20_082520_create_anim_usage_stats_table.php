<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AnimType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anim_usage_stats', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->enum( 'anim', array_column(AnimType::cases(), 'value') );
            $table->char('country', 2);
            $table->string('city', 256);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anim_usage_stats');
    }
};
