<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('website_url')->nullable()->after('youtube_url');
            $table->string('instagram_url')->nullable()->after('website_url');
            $table->string('facebook_url')->nullable()->after('instagram_url');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('twitter_url');
            $table->string('linkedin_url')->nullable()->after('tiktok_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'website_url', 'instagram_url', 'facebook_url',
                'twitter_url', 'tiktok_url', 'linkedin_url',
            ]);
        });
    }
};
