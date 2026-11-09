<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class Regional3UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'bsrm@regional3.com',
                'password' => 'bsrm@123',
                'name' => 'BSRM User',
                'unit' => 'BUSINESS SUPPORT & RISK MANAGEMENT',
            ],
            [
                'email' => 'planning.delivery@regional3.com',
                'password' => 'Planning@123',
                'name' => 'Planning & Delivery User',
                'unit' => 'PLANNING & DELIVERY',
            ],
            [
                'email' => 'operation.maintenance@regional3.com',
                'password' => 'Operation@123',
                'name' => 'Operation & Maintenance User',
                'unit' => 'OPERATION & MAINTENANCE',
            ],
            [
                'email' => 'marketing.sales@regional3.com',
                'password' => 'Marketing@123',
                'name' => 'Marketing Sales & Solution User',
                'unit' => 'MARKETING, SALES & SOLUTION',
            ],
        ];

        foreach ($users as $userData) {
            $unit = Unit::where('name', $userData['unit'])->firstOrFail();

            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => bcrypt($userData['password']),
                    'unit_id' => $unit->id,
                ]
            );

            echo "Created: {$userData['email']} / {$userData['password']} / Unit: {$unit->name}\n";
        }
    }
}