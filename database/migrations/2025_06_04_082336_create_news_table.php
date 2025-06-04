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
         Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('short_description');
            $table->longText('details');
            $table->string('language');
            $table->string('location');
            $table->date('date');
            $table->time('time');
            $table->string('refer_from')->nullable(); // Inshorts, Newsify
            $table->string('link')->nullable();
            $table->string('tag')->nullable();
            $table->string('author')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('favourite')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
