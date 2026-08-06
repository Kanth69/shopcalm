<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->catchPhrase();
        $sellingPrice = $this->faker->numberBetween(1000, 150000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => $this->faker->unique()->ean13(),
            'short_description' => $this->faker->sentence(20),
            'description' => $this->faker->paragraph(10),
            'selling_price' => $sellingPrice,
            'offer_price' => $this->faker->optional(0.4)->numberBetween(500, $sellingPrice - 100),
            'stock' => $this->faker->numberBetween(0, 200),
            'status' => 'Active',
            'featured' => $this->faker->boolean(15),
            'trending' => $this->faker->boolean(25),
            'main_image' => 'https://via.placeholder.com/800x800.png?text=Shopcalm+Product',
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Product $product) {
            // This logic could be expanded later if needed
        })->state(function (array $attributes) {
            $category = Category::inRandomOrder()->first();
            $brand = Brand::where('category_id', $category->id)->inRandomOrder()->first();

            // If a category has no brands, find a brand from another category
            if (!$brand) {
                $brand = Brand::inRandomOrder()->first();
            }

            return [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
            ];
        });
    }
}
