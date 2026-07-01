<?php

namespace Database\Seeders;

use App\Domain\Master\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'TONER', 'name' => 'Toner Printer'],
            ['code' => 'STORAGE', 'name' => 'Storage'],
            ['code' => 'KEYBOARD', 'name' => 'Keyboard'],
            ['code' => 'MOUSE', 'name' => 'Mouse'],
            ['code' => 'MONITOR', 'name' => 'Monitor'],
            ['code' => 'TV', 'name' => 'TV'],
            ['code' => 'NETWORK', 'name' => 'Network Device'],
            ['code' => 'SPAREPART', 'name' => 'Sparepart IT'],
            ['code' => 'CONSUMABLE', 'name' => 'Consumable IT'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'is_active' => true]
            );
        }
    }
}
