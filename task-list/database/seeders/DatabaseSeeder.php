<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Test;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Test::factory(5)->create();
        User::factory(10)->create();
        User::factory(2)->unverified()->create();
        Task::factory(20)->create(); //can also be written \App\Models\Task:: if you don't have use statement
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
