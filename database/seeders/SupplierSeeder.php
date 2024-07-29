<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'Supplier A',
                'phone_number' => '1234567890',
                'location' => 'City A',
                'note' => 'Reliable supplier',
            ],
            [
                'name' => 'Supplier B',
                'phone_number' => '9876543210',
                'location' => 'City B',
                'note' => 'New supplier',
            ],
            // Add more suppliers as needed
        ]);
    }
}
