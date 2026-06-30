<?php

use App\Models\SearchQuery;
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
        Schema::create('search_query_term', function (Blueprint $table) {
            $table->foreignIdFor(SearchQuery::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Term::class)->constrained()->cascadeOnDelete();

            $table->primary(['search_query_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_query_term');
    }
};
