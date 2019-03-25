<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\MemberScore
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $order_id 订单ID
 * @property int $order_score 订单积分(增加为+，减少为-)
 * @property int $method 积分方式（1-消费获得、2-活动获得、10-退款减少、11-兑换减少）
 * @property int $activity_id 如果积分获得方式为活动，则记录活动ID
 * @property int $usable_score 当前可用积分
 * @property int $total_score 当前总积分
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore ofWhen($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereOrderScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUsableScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore withoutTrashed()
 * @mixin \Eloquent
 * @property int $source_id 关联id
 * @property string|null $source_type 订单类型
 * @property int $score_change 积分变动(增加为+，减少为-)
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereScoreChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereSourceType($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @property string|null $description
 * @property-read mixed $method_text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereDescription($value)
 */
class MemberScore extends Model
{
    const METHOD = [
        'cost' => 1, //消费获得
        'active' => 2,//活动获得
        'mall_refund' => 3,//积分商城退单
        'vka' => 4, //vka获取数据
        'star_update' => 5, //星球会员升级瞬间礼包
        'star_date' => 6, //会员日消费获取积分
        'task' => 7, //任务获得
        'game' => 8, //游戏获取
        'custom' => 9, //客服补录
        'refund' => 10,//退款减少
        'change' => 11,//兑换减少
        'expire' => 12 //到期扣减
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'member_scores';
    protected $appends = [
        'method_text'
    ];
    protected $guarded = [
        'id',
    ];
    public static $base = [
        'member_scores.id', 'member_scores.user_id', 'member_scores.source_id', 'member_scores.source_type',
        'member_scores.score_change', 'member_scores.method', 'member_scores.description', 'member_scores.created_at'
    ];

    protected $fillable = ['id', 'user_id', 'source_id', 'source_type', 'score_change', 'method', 'description',
        'member_type', 'created_at', 'task_log_id', 'status'];

    public function getMethodTextAttribute()
    {
        $method = $this->getAttribute('method');
        switch ($method){
            case self::METHOD['cost']:
                return '消费获得';
            case self::METHOD['active']:
                return'活动获得';
            case self::METHOD['star_update']:
                return '星球会员升级瞬间礼包';
            case self::METHOD['mall_refund']:
                return '积分商城退单';
            case self::METHOD['vka']:
                return 'vka数据迁移';
            case self::METHOD['star_date']:
                return '会员日消费获取积分';
            case self::METHOD['task']:
                return '任务获取';
            case self::METHOD['game']:
                return '游戏获取';
            case self::METHOD['custom']:
                return '客服补录';
            case self::METHOD['refund']:
                return'退款减少';
            case self::METHOD['change']:
                return'兑换减少';
            case self::METHOD['expire']:
                return'到期扣减';
//            case self::METHOD['buy_card']:
//                return '购买星球会员卡获得';
        }
    }

    public function scopeOfWhen($query, $id)
    {
        return $query->where('user_id', $id);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }
}
