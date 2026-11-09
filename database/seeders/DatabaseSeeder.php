<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (Unit::DEFAULT_NAMES as $unitName) {
            Unit::query()->firstOrCreate(['name' => $unitName]);
        }

        foreach (DocumentType::DEFAULT_NAMES as $typeName) {
            DocumentType::query()->firstOrCreate(['name' => $typeName]);
        }

        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Monitoring',
                'password' => bcrypt('password'),
            ]
        );
    }
}
