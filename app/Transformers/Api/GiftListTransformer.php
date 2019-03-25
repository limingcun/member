<?php


namespace App\Transformers\Api;


use App\Models\GiftRecord;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class GiftListTransformer extends TransformerAbstract
{
    const GIFT_STATUS = [
        'unclaimed' => 0, //未领取
        'picked' => 1, //已领取
        'overdue' => 2 //已过期
    ];
    public function transform(GiftRecord $giftRecord)
    {
        $level = $giftRecord->level()->select(['id', 'name'])->first();
        return [
            'id' => $giftRecord->id,
            'name' => $level->name . $giftRecord->name,
            'content' => $this->giftContent($level, $giftRecord->gift_type),
            'status' => $this->giftSatus($giftRecord),
            'type' => $giftRecord->gift_type
        ];
    }

    /**
     * 礼包状态
     */
    public function giftSatus($giftRecord)
    {
        if ($giftRecord->pick_at) {
            return self::GIFT_STATUS['picked'];
        } else if ($giftRecord->overdue_at < Carbon::now()) {
            return self::GIFT_STATUS['overdue'];
        }
        return self::GIFT_STATUS['unclaimed'];
    }

    /**
     * 礼包内容
     */
    public function giftContent($level, $gify_type)
    {
        $level = substr($level->name, 2);
        if ($gify_type == GiftRecord::GIFT_TYPE['go_update_cash']) {
            switch ($level) {
                case $level < 5:
                    return '满120减5券 x3';
                case $level < 10:
                    return '满110减10券 x3';
                case $level < 15:
                    return '满110减15券 x3';
                case $level < 20:
                    return '满100减15券 x3';
                case $level < 25:
                    return '满100减20券 x3';
                case $level < 31:
                    return '满100减25券 x3';
            }
        } elseif ($gify_type == GiftRecord::GIFT_TYPE['go_update_buy_fee']) {
            switch ($level) {
                case $level < 5:
                    return '买6赠1券 x2';
                case $level < 10:
                    return '买5赠1券 x2';
                case $level < 15:
                    return '买4赠1券 x3';
                case $level < 20:
                    return '买3赠1券 x3';
                case $level < 25:
                    return '买3赠1券 x3';
                case $level < 31:
                    return '买3赠1券 x3';
            }
        }
    }
}
