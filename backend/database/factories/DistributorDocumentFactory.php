<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\DistributorDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributorDocument>
 */
class DistributorDocumentFactory extends Factory
{
    protected $model = DistributorDocument::class;

    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'title' => fake()->randomElement(['Trading License', 'Tax Certificate', 'Company Registration']),
            'type' => fake()->randomElement(['license', 'certificate', 'registration']),
            'file_path' => 'storage/distributors/documents/'.fake()->uuid().'.pdf',
            'version' => 1,
        ];
    }
}
