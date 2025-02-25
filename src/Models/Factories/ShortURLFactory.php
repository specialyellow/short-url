<?php

namespace SpecialYellow\ShortURL\Models\Factories;

use SpecialYellow\ShortURL\Classes\KeyGenerator;
use SpecialYellow\ShortURL\Models\ShortURL;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends Factory<ShortURL>
 */
class ShortURLFactory extends Factory
{
    protected $model = ShortURL::class;

    public function definition(): array
    {
        $urlKey = (new KeyGenerator())->generateRandom();

        return [
            'user_id'        => 0,
            'campaign_id'    => 1,
            'nickname'       => 'nickname',
            'destination_url' => 'https://google.com',
            'default_short_url' => Str::random(30),
            'url_key' => $urlKey,
            'single_use' => $this->faker->boolean(),
            'forward_query_params' => $this->faker->boolean(),
            'track_visits' => $this->faker->boolean(),
            'username' => $this->faker->name(),
            'notes' => $this->faker->sentence(),
            'redirect_status_code' => $this->faker->randomElement([301, 302]),
            'track_ip_address' => $this->faker->boolean(),
            'track_operating_system' => $this->faker->boolean(),
            'track_operating_system_version' => $this->faker->boolean(),
            'track_browser' => $this->faker->boolean(),
            'track_browser_version' => $this->faker->boolean(),
            'track_referer_url' => $this->faker->boolean(),
            'track_device_type' => $this->faker->boolean(),
            'activated_at' => now(),
            'deactivated_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @return ShortURLFactory
     */
    public function deactivated(): ShortURLFactory
    {
        return $this->state(function () {
            return [
                'deactivated_at' => now()->subDay(),
            ];
        });
    }

    /**
     * @return ShortURLFactory
     */
    public function inactive(): ShortURLFactory
    {
        return $this->state(function () {
            return [
                'activated_at' => null,
                'deactivated_at' => null,
            ];
        });
    }
}
