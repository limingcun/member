<?php

use Illuminate\Database\Seeder;
class UpdateUsersTableSeeder extends Seeder
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
            \DB::update("update users u RIGHT JOIN (select user_id, max(paid_at) as paid_at, count(1) as trade_time, sum(payment) as payment from orders where paid_at is not null and refund_status != 'FULL_REFUND' and user_id > 0 group by user_id) as b 
                        on b.user_id = u.id set u.last_trade_at = b.paid_at, u.sum_trade_times = b.trade_time, u.sum_trade_fee = b.payment");
            \Log::info('upd:users success', ['success']);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('upd:users error', [$exception]);
        }
    }
}
