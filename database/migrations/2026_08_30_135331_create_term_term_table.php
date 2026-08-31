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
        Schema::create('term_term', function (Blueprint $table) {
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->foreignId('related_term_id')->constrained('terms')->cascadeOnDelete();
            $table->primary(['term_id', 'related_term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('term_term');
    }
};
