<?php

use App\Models\MAdmin;
use EasyWeChat\Work\Application;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/10/30
 * Time: 下午4:38
 */
class ImportMAdminSeeder extends Seeder
{
    public function run(Application $work)
    {
        DB::table('m_admins')->insert([
            'no' => 'istore001',
            'name' => '超级管理员',
            'email' => 'admin',
            'sex' => 1,
            'mobile' => '123456',
            'department' => '系统',
            'status' => 1,
            'password' => Hash::make('123456'),
            'role_id' => 1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $ids = [351, 240, 10, 47, 51];
        $dep = [
            351 => '产品部',
            240 => '数字营销部',
            10 => '财务管理中心',
            47 => '品牌部',
            51 => '策划部',
        ];
        foreach ($ids as $id) {
            $users = $work->user->getDetailedDepartmentUsers($id, true)['userlist'];
            foreach ($users as $user) {
                if (MAdmin::where(['email' => $user['email']])->first()) continue;
                MAdmin::create([
                    'name' => $user['name'],
                    'mobile' => $user['mobile'],
                    'sex' => $user['gender'],
                    'email' => $user['email'],
                    'department' => $dep[$id],
                    'no' => sprintf("istore%03d", MAdmin::max('id') + 1),
                    'password' => \Hash::make('123456'),
                ]);
            }
        }
    }
}