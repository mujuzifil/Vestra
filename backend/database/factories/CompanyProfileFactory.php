<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyProfile>
 */
class CompanyProfileFactory extends Factory
{
    protected $model = CompanyProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company(),
            'industry' => $this->faker->randomElement(['Technology', 'Manufacturing', 'Healthcare', 'Finance', 'Agriculture']),
            'business_type' => $this->faker->randomElement(['Limited Company', 'Partnership', 'Sole Proprietorship']),
            'tax_identification' => $this->faker->numerify('TIN-########'),
            'registration_number' => $this->faker->numerify('REG-########'),
            'website' => $this->faker->optional()->url(),
            'district' => $this->faker->city(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'address' => $this->faker->address(),
            'primary_contact_name' => $this->faker->name(),
            'primary_contact_phone' => $this->faker->phoneNumber(),
            'primary_contact_email' => $this->faker->safeEmail(),
            'status' => $this->faker->randomElement(CompanyStatus::cases())->value,
            'account_manager_id' => null,
            'region' => $this->faker->randomElement(['East Africa', 'West Africa', 'Southern Africa', 'Europe', 'Asia']),
            'notes' => null,
        ];
    }
}
