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
        Schema::create('sources', function (Blueprint $table) {
           $table->id();
           $table->string('slug')->unique();
           $table->string('name');
           $table->string('base_url')->nullable();
           $table->string('last_cursor')->nullable();
           $table->timestamp('last_success_at')->nullable();
           $table->boolean('is_enabled')->default(true);
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
