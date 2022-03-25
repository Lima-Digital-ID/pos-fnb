<?php

use Illuminate\Database\Seeder;
use App\PriceCategories;

class PriceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PriceCategories::insert(
            [
                ['category' => 'Customer'],
                ['category' => 'Gojek'],
                ['category' => 'Grab'],
                ['category' => 'Shoope'],
            ]
        );
    }
}
