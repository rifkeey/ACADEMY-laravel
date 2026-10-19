<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** `php artisan db:seed`: real catalogue everywhere; demo accounts only outside production. */
    public function run(): void
    {
        $this->call(CatalogueSeeder::class);

        if (! app()->isProduction()) {
            $this->call(DevAccountsSeeder::class);
        }
    }
}
