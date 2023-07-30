<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('analytics.table_prefix').'page_views', function ($table) {
            $table->id();
            $table->string('uri', 255);
            $table->string('session', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->string('country', 255);
            $table->string('browser', 255)->nullable();
            $table->string('device', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop(config('analytics.table_prefix').'page_views');
    }
};