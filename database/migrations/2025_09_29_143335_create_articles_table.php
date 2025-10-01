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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('source_id')->constrained('sources');
            $table->string('external_id');                      // provider id (article_id)
            $table->string('canonical_url')->index();           // link
            $table->string('title');
            $table->text('summary')->nullable();                // description
            $table->longText('content')->nullable();
            $table->string('language', 8)->nullable()->index(); // 'en'
            $table->timestamp('published_at')->index();
            $table->string('image_url')->nullable();
            $table->string('canonical_url_hash', 64)->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->unique(['source_id','external_id']);
            $table->index(['source_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
