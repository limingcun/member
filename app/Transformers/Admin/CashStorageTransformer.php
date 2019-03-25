<?php

namespace App\Transformers\Admin;

use App\Models\CashStorage;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CashStorageTransformer extends TransformerAbstract
{
    const SEX = [
        'male' => '男',
        'female' => '女',
        'unknow' => '保密',
        '' => '保密'
    ];
    /**
     * 
     * 储值数据获取和转化
     * @return array
     */
    
    public function transform(CashStorage $cash_storage)
    {
        return [
            'id' => $cash_storage->id,
            'user_id' => $cash_storage->user_id,
            'name' => $cash_storage->user->name ?? '',
            'phone' => $cash_storage->user->phone ?? '',
            'birthday' => $cash_storage->user->birthday ?? '',
            'sex' => self::SEX[$cash_storage->user->sex ?? ''],
            'register_time' => $cash_storage->storage_start,
            'storage_way' => CashStorage::STORAGEWAY[$cash_storage->storage_way],
            'status' => CashStorage::STATUS[$cash_storage->status],
            'member_type' => $this->memberType($cash_storage->member ?? ''),
            'total_money' => $cash_storage->total_money,
            'consume_money' => $cash_storage->consume_money,
            'free_money' => $cash_storage->free_money
        ];
    }
    
    /**
     * 判断是星球会员还是Go会员
     * @param type $member
     */
    public function memberType($member) {
        if ($member == '') {
            return 'Go会员';
        }
        if ($member->expire_time) {
            if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
                return '星球会员';
            }
        }
        return 'Go会员';
    }
}
