<?php

use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $permissions = [
            ['name' => 'shop_show', 'label' => '门店查看'],
            ['name' => 'shop_edit', 'label' => '门店编辑'],
            ['name' => 'shop_efficacy_show', 'label' => '门店效能查看'],
            ['name' => 'shop_efficacy_edit', 'label' => '门店效能设置'],
            ['name' => 'product_show', 'label' => '商品查看'],
            ['name' => 'product_edit', 'label' => '商品编辑'],
            ['name' => 'product_category_show', 'label' => '商品分类查看'],
            ['name' => 'product_category_edit', 'label' => '商品分类编辑'],
            ['name' => 'product_material_show', 'label' => '商品加料查看'],
            ['name' => 'product_material_edit', 'label' => '商品加料编辑'],
            ['name' => 'product_attribute_show', 'label' => '商品属性查看'],
            ['name' => 'product_attribute_edit', 'label' => '商品属性编辑'],
            ['name' => 'order_show', 'label' => '预约单查看'],
            ['name' => 'order_refund_show', 'label' => '退款单查看'],
            ['name' => 'order_refund_refund', 'label' => '订单退款'],
            ['name' => 'order_takeaway_show', 'label' => '外卖单查看'],
            ['name' => 'policy_show', 'label' => '策略查看'],
            ['name' => 'policy_edit', 'label' => '策略编辑'],
            ['name' => 'policy_category_show', 'label' => '策略分类查看'],
            ['name' => 'policy_category_edit', 'label' => '策略分类编辑'],
            ['name' => 'policy_execution', 'label' => '执行策略'],
            ['name' => 'period_show', 'label' => '时间段查看'],
            ['name' => 'period_setting', 'label' => '时间段配置'],
            ['name' => 'device_show', 'label' => '设备查看'],
            ['name' => 'ad_show', 'label' => '广告查看'],
            ['name' => 'ad_edit', 'label' => '广告编辑'],
            ['name' => 'user_show', 'label' => '用户查看'],
            ['name' => 'admin_show', 'label' => '系统用户查看'],
            ['name' => 'admin_edit', 'label' => '系统用户编辑'],
            ['name' => 'role_edit', 'label' => '角色权限设置'],
        ];

        foreach ($permissions as $key => $permission) {
            $permissions[$key]['guard_name'] = 'admin';
            $permissions[$key]['created_at'] = $now;
            $permissions[$key]['updated_at'] = $now;
        }

        Permission::insert($permissions);
    }
}
