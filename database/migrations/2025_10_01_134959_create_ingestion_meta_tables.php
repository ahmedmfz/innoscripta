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
        Schema::create('source_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('last_cursor')->nullable();
            $table->timestamp('last_published_at')->nullable();
            $table->timestamps();
            $table->unique('source_id');
        });

        Schema::create('fetch_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 32)->default('started'); // started|ok|failed
            $table->unsignedInteger('items_fetched')->default(0);
            $table->unsignedInteger('items_upserted')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['source_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fetch_runs');
        Schema::dropIfExists('source_states');
    }
};
