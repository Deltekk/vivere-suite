<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * core.notification_attachments: documenti allegati a una notifica.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('core.notification_attachments', function (Blueprint $table) {
            // ----- Primary keys -----
            $table->uuid('id')->primary();

            // ----- Data -----
            $table->text('file_path'); // Percorso nel disco di storage dell'applicazione

            // ----- Foreign Keys -----
            $table->foreignUuid('notification_id')->index()->constrained('core.notifications')->cascadeOnDelete();

            // ----- Timestamps -----
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core.notification_attachments');
    }
};
