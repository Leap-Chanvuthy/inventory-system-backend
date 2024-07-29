<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('inventories')->insert([
            [
                'date' => '2024-07-28',
                'item_name' => 'Product A',
                'description' => 'Product A description',
                'quantity' => 100,
                'cost_per_item' => 10.00,
                'total_value' => 1000.00,
                'category' => 'Category A',
                'unit' => 'pcs',
                'supplier_id' => 1, // Assuming Supplier A has id 1
            ],
            [
                'date' => '2024-07-28',
                'item_name' => 'Product B',
                'description' => 'Product B description',
                'quantity' => 50,
                'cost_per_item' => 20.00,
                'total_value' => 1000.00,
                'category' => 'Category B',
                'unit' => 'box',
                'supplier_id' => 2, // Assuming Supplier B has id 2
            ],
            // Add more inventory items as needed
        ]);
    }
}
