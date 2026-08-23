<?php

namespace Database\Factories;

use App\Models\SyncLog;
use App\Models\SyncLogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyncLogEntryFactory extends Factory
{
    protected $model = SyncLogEntry::class;

    public function definition(): array
    {
        return [
            'sync_log_id' => SyncLog::factory(),
            'level' => fake()->randomElement(['info', 'warning', 'error']),
            'message' => fake()->sentence(),
            'context' => null,
            'created_at' => fake()->dateTimeThisMonth(),
        ];
    }

    public function info(): static
    {
        return $this->state(fn () => ['level' => 'info']);
    }

    public function warning(): static
    {
        return $this->state(fn () => ['level' => 'warning']);
    }

    public function error(): static
    {
        return $this->state(fn () => ['level' => 'error']);
    }
}
