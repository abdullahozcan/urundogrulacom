<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'image' => null,
            'type' => 'physical',
            'digital_content_type' => null,
            'digital_file_path' => null,
            'qr_mode' => 'unique',
            'general_qr_code' => null,
        ];
    }
}

