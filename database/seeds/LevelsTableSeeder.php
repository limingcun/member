<?php

use Illuminate\Database\Seeder;

class LevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('levels')->truncate();
        \DB::table('star_levels')->truncate();
        //插入等级数据
        $levels = [
            ['name' => 'Lv1', 'exp_min' => 0, 'exp_max' => 299],
            ['name' => 'Lv2', 'exp_min' => 300, 'exp_max' => 499],
        ];
        $this->setLevels(3, 13, 500, $levels);
        $this->setLevels(14, 18, 1000, $levels);
        $this->setLevels(19, 23, 2000, $levels);
        $this->setLevels(24, 30, 5000, $levels);
        // Lv30 exp_max无上限
        $levels['29']['exp_max'] = 99999999;
        foreach ($levels as $level) {
            \App\Models\Level::create($level);
        }

        //星球会员等级数据
        $now = \Carbon\Carbon::now();
        $star_levels = [
            ['name' => '白银', 'exp_min' => 0, 'exp_max' => 500, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '黄金', 'exp_min' => 501, 'exp_max' => 2000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '铂金', 'exp_min' => 2001, 'exp_max' => 4000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '钻石', 'exp_min' => 4001, 'exp_max' => 7000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '黑金', 'exp_min' => 7001, 'exp_max' => 11000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '黑钻', 'exp_min' => 11001, 'exp_max' => 9999999, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('star_levels')->insert($star_levels);
    }

    protected function setLevels($start, $end, $step, &$levels)
    {
        for ($i=$start; $i<=$end; $i++){
            $levels[] = [
                'name' => "Lv{$i}",
                'exp_min' => $levels[$i-2]['exp_max']+1,
                'exp_max' => $levels[$i-2]['exp_max']+$step,
            ];
        }
    }
}
