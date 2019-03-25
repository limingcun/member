<?php

use Illuminate\Database\Seeder;

class CouponUpdateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::update('update coupons set flag = 2 where flag = 1');
        \DB::update('update coupons set flag = 1 where flag = 0');
        \DB::update('update coupons set status = 1 where status = 0');
        \DB::update("update coupon_grands as g right join (select id, name, no, count from coupons) as c on g.coupon_id = c.id set g.name = c.name, g.count = c.count, g.no = replace(c.no, 'TN', 'VN')");
    }
}
