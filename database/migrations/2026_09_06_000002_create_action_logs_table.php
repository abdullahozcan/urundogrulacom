<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_rule_id')->constrained('action_rules')->cascadeOnDelete();
            $table->foreignId('scan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('result', ['success', 'failed', 'skipped']);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['action_rule_id', 'scan_id']);
            $table->index(['action_rule_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
