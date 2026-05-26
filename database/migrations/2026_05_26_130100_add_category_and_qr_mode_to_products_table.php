<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('company_id')->constrained('categories')->nullOnDelete();
            $table->enum('qr_mode', ['unique', 'general'])->default('unique')->after('digital_file_path');
            $table->string('general_qr_code')->nullable()->after('qr_mode');
            $table->index('general_qr_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'qr_mode', 'general_qr_code']);
        });
    }
};

