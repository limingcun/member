<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations;


class MemberExp extends Model
{
    const METHOD = [
        'init' => 0,  //经验值初始
        'cost' => 1, //消费获得
        'active' => 2,//活动获得
        'vka' => 3, //vka获取数据
        'task' => 4, //任务获得
        'game' => 5, //游戏获取
        'custom' => 7, //客服补录
        'refund' => 10//退款减少
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'member_exps';

    protected $fillable = ['id', 'user_id', 'source_id', 'source_type', 'go_exp_change', 'star_exp_change', 'method', 'description', 'level_id', 'star_level_id', 'member_type'];

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

    public function getMethodTextAttribute()
    {
        $method = $this->getAttribute('method');
        switch ($method){
            case self::METHOD['init']:
                return '经验值初始数据';
            case self::METHOD['cost']:
                return '消费获得';
            case self::METHOD['active']:
                return'活动获得';
            case self::METHOD['vka']:
                return 'vka数据迁移';
            case self::METHOD['task']:
                return'任务获得';
            case self::METHOD['game']:
                return'游戏获取';
            case self::METHOD['custom']:
                return'客服补录';
            case self::METHOD['refund']:
                return '退款减少';
        }
    }

    public static function createMemberExp($member, $user_id, $source_id, $source_type, $method, $go_exp_change, $star_exp_change, $description) {
        $is_star = Member::isStarMember($member);
        $member_type = 0;
        if ($is_star) {
            $member_type = 1;
        }
        MemberExp::create([
            'user_id' => $user_id,
            'source_id' => $source_id,
            'source_type' => $source_type,
            'method' => $method,
            'go_exp_change' => $go_exp_change,
            'star_exp_change' => $star_exp_change,
            'description' => $description,
            'member_type' => $member_type,
            'level_id' => $member->level_id,
            'star_level_id' => $member->star_level_id
        ]);
    }
}
