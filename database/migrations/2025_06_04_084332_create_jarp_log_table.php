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
        Schema::create('jarp_log', function (Blueprint $table) {
        $table->id();
        $table->string('product_type'); // news, movie, shopping
        $table->string('product_id');   // ID of the product
        $table->unsignedBigInteger('user_id');
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('hit_at')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jarp_log');
    }
};
