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
        Schema::create('authors', function (Blueprint $table) {
           $table->id();
           $table->string('name')->unique();
           $table->timestamps();
        });

       Schema::create('categories', function (Blueprint $table) {
           $table->id();
           $table->string('slug')->unique();
           $table->string('label');
           $table->timestamps();
       });

       Schema::create('article_author', function (Blueprint $table) {
           $table->uuid('article_id');
           $table->unsignedBigInteger('author_id');
           $table->primary(['article_id', 'author_id']);
           $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
           $table->foreign('author_id')->references('id')->on('authors')->cascadeOnDelete();
       });

       Schema::create('article_category', function (Blueprint $table) {
           $table->uuid('article_id');
           $table->unsignedBigInteger('category_id');
           $table->primary(['article_id', 'category_id']);
           $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
           $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_category');
        Schema::dropIfExists('article_author');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('authors');
    }
};
