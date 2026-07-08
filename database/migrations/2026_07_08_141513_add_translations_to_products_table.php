<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultLocale = config('languages.fallback', 'en');
        $supported = array_keys(config('languages.supported', ['en']));

        DB::table('products')->orderBy('id')->each(function ($product) use ($defaultLocale, $supported) {
            $nameData = [];
            $descData = [];

            foreach ($supported as $locale) {
                $nameData[$locale] = $product->name;
                $descData[$locale] = strip_tags((string) $product->description);
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'name' => json_encode($nameData),
                    'description' => json_encode($descData),
                ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->change();
        });
    }

    public function down(): void
    {
        $defaultLocale = config('languages.fallback', 'en');

        DB::table('products')->orderBy('id')->each(function ($product) use ($defaultLocale) {
            $name = json_decode($product->name, true);

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'name' => is_array($name) ? ($name[$defaultLocale] ?? '') : (string) $product->name,
                    'description' => null,
                ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });
    }
};
