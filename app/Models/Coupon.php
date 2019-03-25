<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * App\Models\CouponType
 *
 * @property int $id
 * @property string $key 优惠劵标识
 * @property string $name 优惠劵类型名称
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $period_type 过期类型（1绝对时间，2相对时间）
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property int $period_day 有效时间段
 * @property int $count 发券数量
 * @property int $shop_limit 门店限制
 * @property int $product_limit 商品限制
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponGrand[] $grand
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $unuselib
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $uselib
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $product
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shop
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $picklib
 * @property string|null $no 优惠券编号
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereNo($value)
 * @property int $flag 优惠券标识
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Coupon[] $mall
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereFlag($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $order
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @property int $unit_time 时间维度单位,0表示天，1表示月，2表示年
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUnitTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUseLimit($value)
 * @property int $status 模板状态0为已启动，1为已停用
 * @property string|null $image 优惠券模板图片
 * @property string|null $admin_name 创建人
 * @property int $category_limit 饮品类别限制
 * @property int $material_limit 加料限制
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $category
 * @property-read mixed $status_text
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallProduct[] $mallProduct
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Material[] $material
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereAdminName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCategoryLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereMaterialLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereStatus($value)
 */
class Coupon extends Model
{
    const PERIODTYPE = [
        'fixed' => 0, //固定过期
        'relative' => 1 //相对过期
    ];

    const TIMEUNIT = [
        'day' => 0, //天
        'month' => 1, //月
        'year' => 2 //年
    ];

    const DATE = ['天', '月', '年'];

    const USELIMIT = [
        'all' => 0, //全部
        'self' => 1, //自取
        'takeout' => 2 //外卖
    ];

    const UNITIME = [
        'day' => 0, //天
        'month' => 1, //月
        'year' => 2  //年
    ];
    /**
     * 发券规则
     *
     * 1-4 //3 120-5  2 6-1
     * 5-9 //3 110-10 2 5-1
     * 10-14 //3 110-15 3 4-1
     * 15-19 //3 100-15 3 3-1
     * 20-24 //3 100-20 3 3-1
     * 25-30 //3 100-25 3 2-1
     */
    const FLAG = [
        'coupon' => 1, //优惠券
        'mall' => 2, //积分商城
        'take' => 3, //外卖券
        'vka' => 4, //vka迁移数据
        'active' => 5, //活动获取
        'active_gold_pig' => 6, //喜茶金猪活动赠送

        //************* go会员升级 ************
        // go会员升级 现金券模板
        'cash_120-5' => 11, //现金券120-5
        'cash_110-10' => 12,
        'cash_110-15' => 13,
        'cash_100-15' => 14,
        'cash_100-20' => 15,
        'cash_100-25' => 16,
        // go会员升级 买赠券
        'buy_fee_6-1' => 17, //买赠券 买六减一
        'buy_fee_5-1' => 18,
        'buy_fee_4-1' => 19,
        'buy_fee_3-1' => 20,
        'buy_fee_2-1' => 21,
        'buy_fee_1-1' => 22,

        // ************ 星球会员购卡 **********
        // 星球会员购卡奖励 买二送一
        'buy_fee_card_2-1' => 31,
        // 星球会员购卡奖励 买一送一
        'buy_fee_card_1-1' => 32,
        // 星球会员购卡奖励 免运费券
        'fee_card_take_fee' => 33,
        // 星球会员购卡奖励 指定饮品立减券（折扣券）
        'discount_card' => 34,
        // 星球会员购卡奖励 优先券
        'queue_card'  => 35,

        // ************ 星球会员首充 **********
        // 首充奖励 10元现金券
        'cash_star_first' => 41,
        // 星球会员首充奖励 亲友邀请券 买一赠一
        'buy_fee_first_1-1' => 42,

        // ************ 星球会员福利 **********
        // 星球会员每月福利 现金券模板
        'cash_150-5' => 51,
        'cash_150-10' => 52,
        'cash_150-15' => 53,
        'cash_150-20' => 54,
        'cash_150-25' => 55,
        'cash_150-30' => 56,
        // 星球开通纪念日 赠饮券模板
        'fee_star_anniversary' => 57,
        // 星球会员 钻石 满20单 兑换赠饮券
        'fee_star_20' => 58,
        // 星球会员 黑金 满10单 兑换赠饮券
        'fee_star_10' => 59,
        // 星球会员 黑钻 满5单 兑换赠饮券
        'fee_star_5' => 60,
        // 星球会员生日好礼赠饮券
        'fee_star_birthday' => 61,
        // 星球会员5.12会员日赠饮券
        'fee_star_prime_day' => 62,
        // 星球会员每月福利 折扣券模板
        'discount_star_month' => 63,
        // 星球会员每月福利 优先券模板
        'queue_star_month'  => 64,
        // 星球会员每月福利 买赠券模板
        'buy_fee_star_3-1' => 65,
        'buy_fee_star_2-1' => 66,
        'buy_fee_star_1-1' => 67,
        // ************ 星球会员升级礼包 **********
        // 星球会员升级瞬间礼包 赠饮券
        'fee_star_update' => 71,

        // ************ vka移民 **********
        //vka赠饮券模板
        'vka_fee' => 81,
        //vka买赠券模板
        'vka_buyfee' => 82,

        // ************ 员工券 *********
        // 单品五折券
        'hey_tea_discount_5' => 101,
        // 25元代金券
        'hey_tea_cash_25' => 102,
        // 司庆券
        'hey_tea_fee_day' => 103,
        // 入职
        'hey_tea_fee_join_day' => 104,
        // 入职周年
        'hey_tea_fee_join_anniversary' => 105,
        // 员工生日
        'hey_tea_fee_birthday' => 106,
        // 喜茶员工优先券
        'hey_tea_queue' => 107,
        // 9块9邀请活动买一赠一券
        'invite_5_buy_fee' => 110,  // 首次邀请5人领取
        'invite_10_buy_fee' => 111,  // 除首次5人外，每10人领取一张
        
        //春节小游戏
        'spring_game_cash' => 120,  //春节游戏现金券
        'spring_game_fee' => 121,    //春节游戏赠饮券
        'spring_game_queue' => 122  //春节游戏优先券
    ];

    /**
     * 每年5.12会员日赠饮券
     *
     * 白银   会员开通日赠饮券x1  指定饮品立减（折扣券）x1 满减150-5券x2
     * 黄金   会员开通日赠饮券x1  折扣券x2   满减(150-10)x2    优先券x1
     * 铂金   会员开通日赠饮券x1  折扣券x3   满减(150-15)x3    优先券x1   买三送一x2
     * 钻石   会员开通日赠饮券x1  折扣券x3   满减(150-20)x3    优先券x2   买二送一x2      每20单送1张赠饮券
     * 黑金   会员开通日赠饮券x1  折扣券x5   满减(150-25)x3    优先券x2   买二送一x3      每10单送1张赠饮券
     * 黑钻   会员开通日赠饮券x2  折扣券x6   满减(150-30)x3    优先券x3   买一送一x2      每5单送1张赠饮券
     */

    // 优惠券券模板
    const CASH_COUPON_TPL = [
        'cash_120-5' => ['cash_120-5', 120, 5],  //现金券 [flag标识 满120-5]
        'cash_110-10' => ['cash_110-10', 110, 10],
        'cash_110-15' => ['cash_110-15', 110, 15],
        'cash_100-15' => ['cash_100-15', 100, 15],
        'cash_100-20' => ['cash_100-20', 100, 20],
        'cash_100-25' => ['cash_100-25', 100, 25],
        'cash_150-5' => ['cash_150-5', 150, 5],
        'cash_150-10' => ['cash_150-10', 150, 10],
        'cash_150-15' => ['cash_150-15', 150, 15],
        'cash_150-20' => ['cash_150-20', 150, 20],
        'cash_150-25' => ['cash_150-25', 150, 25],
        'cash_150-30' => ['cash_150-30', 150, 30],
    ];

    // 买赠券模板
    const BUY_FEE_COUPON_TPL = [
        'buy_fee_6-1' => ['buy_fee_6-1', 6, 1], //赠饮券 满6赠1
        'buy_fee_5-1' => ['buy_fee_5-1', 5, 1],
        'buy_fee_4-1' => ['buy_fee_4-1', 4, 1],
        'buy_fee_3-1' => ['buy_fee_3-1', 3, 1],
        'buy_fee_2-1' => ['buy_fee_2-1', 2, 1],
        'buy_fee_1-1' => ['buy_fee_1-1', 1, 1],
        'buy_fee_first_1-1' => ['buy_fee_first_1-1', 1, 1],    // 星球会员首充奖励 亲友邀请券 买一赠一
        'buy_fee_star_3-1' => ['buy_fee_star_3-1', 3, 1],
        'buy_fee_star_2-1' => ['buy_fee_star_2-1', 2, 1],
        'buy_fee_star_1-1' => ['buy_fee_star_1-1', 1, 1],
    ];

    const STATUS = [
        'start' => 0, //待启用
        'used' => 1, //已使用
        'end' => 2, //已停用
        'period' => 3 //已过期
    ];

    use SoftDeletes;
    protected $dates = [
        'deleted_at',
        'period_start',
        'period_end'
    ];
    protected $table = 'coupons';
    protected $fillable = [
        'name',
        'policy',
        'policy_rule',
        'period_type',
        'period_start',
        'period_end',
        'period_day',
        'count',
        'shop_limit',
        'product_limit',
        'category_limit',
        'material_limit',
        'no',
        'count',
        'status',
        'flag',
        'unit_time',
        'use_limit',
        'admin_name',
        'image',
        'interval_time'
    ];
    protected $casts = [
        'policy_rule' => 'array',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    public function shop()
    {
        return $this->belongsToMany(Shop::class, 'coupon_shops');
    }

    public function product()
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function material()
    {
        return $this->belongsToMany(Material::class, 'coupon_materials');
    }

    public function library()
    {
        return $this->hasMany(CouponLibrary::class);
    }

    public function grand()
    {
        return $this->hasOne(CouponGrand::class);
    }

    public function order() {
        return $this->belongsToMany(Order::class, 'coupon_librarys');
    }

    function getStatusTextAttribute($value)
    {
        switch($value) {
            case self::STATUS['start']:
                return '待启用';
            case self::STATUS['used']:
                return '已使用';
            case self::STATUS['end']:
                return '已停用';
            case self::STATUS['period']:
                return '已过期';
        }
    }

    public function getImageAttribute($value) {
        if ($value) {
            return env('QINIU_URL').$value;
        }
    }

     /*
     * 时间维度有效期
     */
    public static function getTimePeriod(Coupon $coupon, $time = null) {
        $time = $time ?? Carbon::now();
        switch($coupon->unit_time) {
            case self::TIMEUNIT['day']:
                return $time->addDays($coupon->period_day)->endOfDay();
            case self::TIMEUNIT['month']:
                return $time->addMonths($coupon->period_day)->endOfDay();
            case self::TIMEUNIT['year']:
                return $time->addYears($coupon->period_day)->endOfDay();
            default:
                return null;
        }
    }

    public function mallProduct() {
        return $this->morphMany(MallProduct::class, 'source');
    }

    /*
     * 判断喜茶券模板是否可以编辑
     */
    public static function cutStatus(Coupon $coupon) {
        if($coupon->grand()->count()) {
            return 1;
        } else if ($coupon->mallProduct()->count()) {
            return 1;
        } else {
            return 0;
        }
    }
}
