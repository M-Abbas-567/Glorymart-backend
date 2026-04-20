<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'vendor_id' => \App\Models\Vendor::factory(),
            'category_id' => \App\Models\Category::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => $this->faker->numberBetween(1, 100),
            'image' => $this->faker->imageUrl(900, 900, 'products'),
            'featured' => $this->faker->boolean(20), // 20% chance
        ];
    }
}