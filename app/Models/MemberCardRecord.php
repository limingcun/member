<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * App\Models\MemberCardRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $card_no 卡号
 * @property int $card_type 会员卡类型
 * @property float $price
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCartType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord withoutTrashed()
 * @mixin \Eloquent
 */
class MemberCardRecord extends Model
{
    const CARD_TYPE = [
        'experience' => 1,  // 体验卡
        'monthly' => 2,     // 月卡
        'season' => 3,      // 季卡
        'half_year' => 4,   // 半年卡
        'annual' => 5,      // 年卡
        'vka' => 6         // vka
    ];

    // paid_type 支付方式 1微信支付 3后台赠送 4兑换码兑换

    // 每种卡对应的sku no
    const SKU = [
        self::CARD_TYPE['experience'] => '39010001',    // 体验卡
        self::CARD_TYPE['season'] => '39010002',    // 季卡
        self::CARD_TYPE['half_year'] => '39010003',    // 半年卡
        self::CARD_TYPE['annual'] => '39010004',    // 年卡
    ];

    const CARD_NAME = [
        self::CARD_TYPE['experience'] => '体验卡',
        self::CARD_TYPE['season'] => '季卡',
        self::CARD_TYPE['half_year'] => '半年卡',
        self::CARD_TYPE['annual'] => '年卡'
    ];

    const PRICE = [
        'experience' => 9.9,  // 体验卡
//        'monthly' => 30,     // 月卡
        'season' => 59,      // 季卡
        'half_year' => 99,   // 半年卡
        'annual' => 179,      // 年卡
    ];

    const STATUS = [
        'wait_pay' => 0,
        'is_pay' => 1,
        'cancel' => 2,   // 关闭的订单
        'refund' => 3    // 退款的订单
    ];


    public static function getPrice($card_type)
    {
        switch ($card_type) {
            case self::CARD_TYPE['experience']:
                return self::PRICE['experience'];
            case self::CARD_TYPE['season']:
                return self::PRICE['season'];
            case self::CARD_TYPE['half_year']:
                return self::PRICE['half_year'];
            case self::CARD_TYPE['annual']:
                return self::PRICE['annual'];
            default:
                \Log::error('Card price error in buy card ... error type = ' . $card_type);
                return false;
        }
    }


    // 返回会员卡结束日期
    public static function getPeriodEnd($card_type, Carbon $periodStart)
    {
        switch ($card_type) {
            case self::CARD_TYPE['experience']:
                return $periodStart->addDays(15);
            case self::CARD_TYPE['season']:
                return $periodStart->addMonthsNoOverflow(3);
            case self::CARD_TYPE['half_year']:
                return $periodStart->addMonthsNoOverflow(6);
            case self::CARD_TYPE['annual']:
                return $periodStart->addMonthsNoOverflow(12);
            default:
                \Log::error('Card type error in buy card ... error type = ' . $card_type);
                return false;
        }
    }

    public static function getCardCoupon($card_type) {
        switch ($card_type) {
            case MemberCardRecord::CARD_TYPE['experience']:
                return [
                    'id' => MemberCardRecord::CARD_TYPE['experience'],
                    'name' => '体验卡',
                    'price' => MemberCardRecord::PRICE['experience'],
                    'coupon' => [
                        ['name' => '限定饮品9折券', 'amount' => 1,],
                        ['name' => '买一赠一', 'amount' => 1,],
                        ['name' => '买二赠一', 'amount' => 1,],
                    ],
                ];
            case MemberCardRecord::CARD_TYPE['season']:
                return [
                    'id' => MemberCardRecord::CARD_TYPE['season'],
                    'name' => '季卡',
                    'price' => MemberCardRecord::PRICE['season'],
                    'coupon' => [
                        ['name' => '限定饮品9折券', 'amount' => 2,],
                        ['name' => '买一赠一', 'amount' => 2,],
                        ['name' => '买二赠一', 'amount' => 3,],
                        ['name' => '优先券', 'amount' => 1],
                    ]
                ];
            case MemberCardRecord::CARD_TYPE['half_year']:
                return [
                    'id' => MemberCardRecord::CARD_TYPE['half_year'],
                    'name' => '半年卡',
                    'price' => MemberCardRecord::PRICE['half_year'],
                    'coupon' => [
                        ['name' => '限定饮品9折券', 'amount' => 3,],
                        ['name' => '买一赠一', 'amount' => 3,],
                        ['name' => '买二赠一', 'amount' => 4,],
                        ['name' => '优先券', 'amount' => 1],
                    ]
                ];
            case MemberCardRecord::CARD_TYPE['annual']:
                return [
                    'id' => MemberCardRecord::CARD_TYPE['annual'],
                    'name' => '年卡',
                    'price' => MemberCardRecord::PRICE['annual'],
                    'coupon' => [
                        ['name' => '限定饮品9折券', 'amount' => 2,],
                        ['name' => '买一赠一', 'amount' => 4,],
                        ['name' => '买二赠一', 'amount' => 5,],
                        ['name' => '免运费券', 'amount' => 2],
                        ['name' => '优先券', 'amount' => 2],
                    ]
                ];
        }
        return [];
    }

    use SoftDeletes;
    protected $table = 'member_card_records';
    protected $dates = ['deleted_at'];  //开启deleted_at

    protected $fillable = ['user_id', 'card_no', 'card_type', 'price', 'period_start', 'period_end', 'prepay_id',
        'order_no', 'trade_type', 'status', 'paid_type', 'paid_at', 'level_change', 'admin_id', 'inviter_id',
        'card_code_order_id', 'no', 'code'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //批量插入数据
    public function maxInsert(Array $data)
    {
        $rs = DB::table($this->getTable())->insert($data);
    }
}
