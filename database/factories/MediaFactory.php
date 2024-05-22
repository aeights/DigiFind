<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_id' => fake()->numberBetween(1,50),
            'media_type_id' => fake()->numberBetween(3,4),
            'file_name' => fake()->lexify('file_???????.jpg'),
            'path' => fake()->lexify('media/??????-report'),
            'url' => fake()->lexify('media/??????-report.jpg'),
            'mime_type' => fake()->randomElement(['png', 'jpg', 'jpeg']),
            'size' => fake()->numberBetween(10000,90000),
        ];
    }
}
