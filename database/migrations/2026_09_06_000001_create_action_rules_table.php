<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('trigger_type', ['scan_count', 'first_scan', 'date_range', 'item_revisit']);
            $table->json('trigger_config')->nullable();
            $table->enum('action_type', ['show_content', 'redirect', 'webhook', 'notify_admin']);
            $table->json('action_payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_executions')->nullable();
            $table->unsignedInteger('times_executed')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'product_id', 'is_active']);
            $table->index(['product_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_rules');
    }
};
