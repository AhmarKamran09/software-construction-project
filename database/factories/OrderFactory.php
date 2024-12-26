<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Create a related User
            'status' => $this->faker->randomElement(['completed']),
            'total' => $this->faker->randomFloat(2, 10, 1000), // Random float between 10 and 1000
            'country' => $this->faker->country,
            'session_id' => $this->faker->uuid,
        ];
    }
}
