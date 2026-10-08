<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.changelogs: novità di ogni piattaforma, mostrate al primo login utile.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.changelogs', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('service');               // Alias del modulo a cui si riferisce (humanresources, kaffettino, ...)
            $table->text('title');
            $table->text('body');
            $table->timestampTz('published_at');   // Visibile solo da questa data: si può preparare la voce prima del rilascio

            // ----- Timestamps -----
            $table->timestampsTz();

            // ----- Indexes -----
            $table->index(['service', 'published_at']); // "Changelog non ancora visti del modulo X", eseguita a ogni login
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.changelogs');
    }
};
