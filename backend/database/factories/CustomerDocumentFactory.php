<?php

namespace Database\Factories;

use App\Models\CustomerDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerDocument>
 */
class CustomerDocumentFactory extends Factory
{
    protected $model = CustomerDocument::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['contract', 'invoice', 'certificate', 'identification']),
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'file_name' => $this->faker->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => random_int(10000, 5000000),
            'documentable_type' => null,
            'documentable_id' => null,
            'metadata' => null,
            'is_downloadable' => true,
        ];
    }
}
