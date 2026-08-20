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
        Schema::create('sync_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_log_id')->constrained()->cascadeOnDelete();
            $table->string('level'); // info, warning, error
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_log_entries');
    }
};
