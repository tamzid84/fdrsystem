<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundSeeder extends Seeder
{
    
    public function run(): void
    {
        DB::table('funds')->insert([
            [
                'name' => 'Membership Fund',
                'code' => 129.15,
                'tax_rate' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Replacement Reserve Fund',
                'code' => 126.2,
                'tax_rate' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Providend Fund',
                'code' => 129.25,
                'tax_rate' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gratuity Fund',
                'code' => 129.35,
                'tax_rate' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Donation Reserve Fund',
                'code' => 121.20,
                'tax_rate' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meter Rent Fund',
                'code' => 129.90,
                'tax_rate' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Employee Security Deposit',
                'code' => 129.40,
                'tax_rate' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}