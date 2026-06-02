<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->date('expiration_date')->nullable()->after('description');
            $table->string('serial_number')->nullable()->after('expiration_date');
            $table->string('youtube_url')->nullable()->after('digital_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['expiration_date', 'serial_number', 'youtube_url']);
        });
    }
};
