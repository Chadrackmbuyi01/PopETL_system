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
        Schema::create('population_record', function (Blueprint $table) {
            $table->id();
            $table->string('country', 100)->index();
            $table->integer('year')->index();
            $table->integer('total_population');
            $table->decimal('population_growth', 8, 4)->nullable();
            $table->decimal('population_density', 12, 4)->nullable();
            $table->bigInteger('male_population')->nullable();
            $table->bigInteger('female_population')->nullable();
            $table->timestamp('extracted_at');
            $table->timestamps();

            // Prevent duplicate records per country and year
            $table->unique(['country', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('population_record');
    }
};
