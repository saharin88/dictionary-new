<?php

use App\Models\Term;
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
        Schema::create('term_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Term::class)->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->longText('description');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('term_proposals');
    }
};
