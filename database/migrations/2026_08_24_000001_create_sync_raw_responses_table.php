<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_raw_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_log_id')->constrained('sync_logs')->cascadeOnDelete();
            $table->enum('resource_type', ['location', 'episode', 'character']);
            $table->unsignedInteger('page_number');
            $table->unsignedInteger('total_pages');
            $table->binary('response_body');
            $table->unsignedInteger('items_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_raw_responses');
    }
};
