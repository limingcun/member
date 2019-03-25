<?php

use Illuminate\Database\Seeder;
class DataRestoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::beginTransaction();
        try {
            \DB::table('member_scores')->truncate();
            \DB::update('update members set order_count = 0, order_money = 0, order_score = 0, usable_score = 0, phone = null, birthday = null');
            \Log::info('res:data success', ['success']);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('res:data error', [$exception]);
        }
    }
}
