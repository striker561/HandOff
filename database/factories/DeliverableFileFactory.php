<?php

namespace Database\Factories;

use App\Models\{Deliverable, User};
use App\Enums\DeliverableFile\MimeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliverableFile>
 */
class DeliverableFileFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->uuid() . '.pdf';
        $originalFilename = fake()->word() . '_' . fake()->word() . '.pdf';

        return [
            'deliverable_unique_id' => Deliverable::factory(),
            'uploaded_by_unique_id' => User::factory(),
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'file_path' => 'deliverables/' . $filename,
            'file_size' => fake()->numberBetween(1024, 10485760), // 1KB to 10MB
            'mime_type' => fake()->randomElement(MimeType::cases()),
            'version' => '1.0',
            'is_latest' => true,
            'download_count' => fake()->numberBetween(0, 50),
            'metadata' => [
                'original_size' => fake()->numberBetween(1024, 10485760),
                'uploaded_from' => fake()->randomElement(['web', 'api', 'mobile']),
            ],
        ];
    }

    public function oldVersion(): static
    {
        return $this->state(fn() => [
            'is_latest' => false,
            'version' => fake()->randomElement(['0.1', '0.5', '0.9']),
        ]);
    }
}
