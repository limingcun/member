<?php

use Illuminate\Database\Seeder;
class UpdateScoresTableSeeder extends Seeder
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
            \DB::update("update member_scores as ms left join (select tmp.order_id, tmp.num, ois.name from ((
                select oi.order_id, count(oi.id) as num from orders as o left join order_items as oi on o.id = oi.order_id group by o.id) tmp left join order_items ois on tmp.order_id = ois.order_id))
                temp on ms.source_id = temp.order_id set ms.description = if(temp.num=1, concat('购买 ', temp.name, ' ', temp.num, ' 件商品'), concat('购买 ', temp.name, ' 等 ', temp.num, ' 件商品'))");
            \Log::info('upd:point success', ['success']);
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('upd:point error', [$exception]);
        }
    }
}
