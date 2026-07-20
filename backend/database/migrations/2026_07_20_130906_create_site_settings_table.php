<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A single-row "globals" table (see SiteSetting::current()). Colours are
     * stored as JSON — the same convention as pages.content / pages.seo — because
     * the shape (an ordered palette + a semantic-colours object) is owned by the
     * DTOs, not the database.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->json('colors')->nullable();
            $table->json('semantic_colors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
