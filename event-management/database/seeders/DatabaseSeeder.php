<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(1000)->create(); //load users

        $this->call(EventSeeder::class); //generate events with a random owner
        $this->call(AttendeeSeeder::class); //generate attendees for that user attends with a random amount they attend
    }
}