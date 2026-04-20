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
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->default('physical')->after('image');
            $table->string('digital_content_type')->nullable()->after('type');
            $table->string('digital_file_path')->nullable()->after('digital_content_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'digital_content_type', 'digital_file_path']);
        });
    }
};
