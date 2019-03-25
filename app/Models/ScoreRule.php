<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ScoreRule
 *
 * @property int $id
 * @property int $score 消费获得积分数[ 消费获得积分=floor(消费金额/rmb_base) ]
 * @property int $rmb_base 每消费RMB数
 * @property int $score_max_per_day 每人每天积分获取上限
 * @property int $months 积分有效月份（0-永久有效、N-N月后某个时间定期清0）
 * @property string $expiry_type 积分有效期类型(1-永不过期、2-定期清0)
 * @property string|null $expiry_time 指定N年后的具体失效日期(md格式，如1231表示，12月31日)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereExpiryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereExpiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRmbBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereScoreMaxPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $remind_type 默认0,1短信提醒，2微信服务通知提醒，两种类型用1,2逗号隔开
 * @property int $remind_time 过期前几天提醒
 * @property string|null $remind_msg 提醒文字
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindType($value)
 */
class ScoreRule extends Model
{
    protected $table = 'score_rules';
    

    protected $guarded=[
        'id',
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
