<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(ProductsTableSeeder::class);
        //$this->call(PermissionTableSeeder::class);
//        $this->call(ScoresTableSeeder::class);
//        $this->call(UpdateScoresTableSeeder::class);
//        $this->call(ImportMAdminSeeder::class);
        $this->call(CouponUpdateTableSeeder::class);
    }
}
