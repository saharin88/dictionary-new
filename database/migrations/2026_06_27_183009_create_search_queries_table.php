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
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('search_query')->unique();
            $table->string('title')->nullable();
            $table->bigInteger('count')->default(1);
            $table->dateTime('last_searched_at')->nullable();
            $table->ipAddress('last_ip')->nullable();
            $table->boolean('have_result')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
