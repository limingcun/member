<?php

use Illuminate\Database\Seeder;
class RecordScoresTableSeeder extends Seeder
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
            $class = addslashes('App\Models\Order');
            \DB::update("update members as m right join (select o.user_id, count(1) as counts, sum(o.payment) as money from orders o LEFT JOIN member_scores ms on o.id = ms.source_id where ms.id is null and o.paid_at is not null "
		. "and o.refund_status = 'NO_REFUND' and o.user_id > 0 and o.created_at between '2018-11-23 00:00:00' and '2018-11-26 23:59:59' group by o.user_id) "
		. "as tmp on m.user_id = tmp.user_id set m.order_count = m.order_count + tmp.counts, m.order_money = round(m.order_money + tmp.money, 2), m.order_score = m.order_score + floor(tmp.money/2), "
		. "m.usable_score = m.usable_score + floor(tmp.money/2)");
            \DB::insert("insert into member_scores(user_id, source_id, source_type, score_change, method, description, created_at, updated_at, deleted_at) "
		. "select o.user_id, o.id, '" .$class. "', floor(o.payment/2), 1, '消费补录积分',  o.created_at, o.updated_at, null from orders o left join member_scores ms on o.id = ms.source_id "
		. "where o.paid_at is not null and o.refund_status = 'NO_REFUND' and o.user_id > 0 and o.created_at between '2018-11-23 00:00:00' and '2018-11-26 23:59:59' and ms.id is null");	
            \DB::commit();
            \Log::info('cha:point_success', ['success']);
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('cha:point_error', [$exception]);
        }
    }
}
