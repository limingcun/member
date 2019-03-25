<?php

use Illuminate\Database\Seeder;
class ScoresTableSeeder extends Seeder
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
            \DB::insert("insert into member_scores(user_id, source_id, source_type, score_change, method, description, created_at, updated_at, deleted_at) "
                    . "select user_id, id, '" .$class. "', floor(payment/2), 1, null,  created_at, updated_at, null from orders where paid_at != '' or paid_at is not null");
            \DB::update('update members as m right join (select user_id, count(1) as counts, sum(payment) as money from orders where paid_at is not null group by user_id) '
                    . 'as tmp on m.user_id = tmp.user_id set m.order_count = tmp.counts, m.order_money = tmp.money');
            \DB::update('update members as m right join (select user_id, sum(score_change) as score_change_total from member_scores group by user_id) as tmp '
                    . 'on m.user_id = tmp.user_id set m.order_score = tmp.score_change_total, m.usable_score = tmp.score_change_total');
            \DB::update('update members as m right join (select id, phone, birthday from users) as u on m.user_id = u.id set m.phone = u.phone, m.birthday = u.birthday');
            \Log::info('cha:point success', ['success']);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('cha:point error', [$exception]);
        }
    }
}
