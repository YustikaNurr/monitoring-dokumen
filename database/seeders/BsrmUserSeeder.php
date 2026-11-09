<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class BsrmUserSeeder extends Seeder
{
    public function run(): void
    {
        $bsrmUnit = Unit::where('name', 'BUSINESS SUPPORT & RISK MANAGEMENT')->firstOrFail();

        User::firstOrCreate(
            ['email' => 'bsrm@regional3.test'],
            [
                'name' => 'BSRM Admin',
                'email' => 'bsrm@regional3.test',
                'password' => bcrypt('password123'),
                'unit_id' => $bsrmUnit->id,
            ]
        );

        echo "BSRM user created successfully!\n";
        echo "Email: bsrm@regional3.test\n";
        echo "Password: password123\n";
        echo "Unit: " . $bsrmUnit->name . "\n";
    }
}