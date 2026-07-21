<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedInteger('menu_order')->default(0)->after('title');
        });

        // Seed a stable initial order from the previous alphabetical behaviour,
        // so the menu keeps its current order until an editor drags it. Done via
        // the query builder to skip the model's saving hook.
        DB::table('pages')->orderBy('title')->pluck('id')
            ->each(fn (int $id, int $i) => DB::table('pages')->where('id', $id)->update(['menu_order' => $i]));
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('menu_order');
        });
    }
};
