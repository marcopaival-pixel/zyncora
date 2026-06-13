<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 999),
            'email' => $this->faker->unique()->companyEmail(),
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 10,
            'max_attendants' => 5,
            'max_channels' => 3,
            'max_chatbots' => 5,
        ];
    }
}
