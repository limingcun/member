<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations;


class Message extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'istore_push_messages';

    const TAB = [
        'new'   => 0,  //0表示未读
        'scan'  => 1  //1表示已读
    ];

    const TITLE = [
        'go_upgrade'        => 'GO会员升级提醒',
        'star_upgrade'      => '会员到期提醒',
        'star_overdue'      => '星球会员升级提醒',
        'coupons_get'       => '喜茶券到账通知',
        'coupons_return'    => '喜茶券退回通知',
        'coupons_overdue'   => '喜茶券到期通知'
    ];


    const CONTENT = [
        'go_upgrade'        => '恭喜您升级啦，点击领取升级奖励',
        'star_upgrade'      => '您的会员身份即将到期，续费尊享会员特权',
        'star_overdue'      => '恭喜您升级啦，点击查看新的等级特权',
        'coupons_get'       => '收到新的喜茶券啦，一起来看看吧',
        'coupons_return'    => '您的喜茶券已退回，点击查看详情',
        'coupons_overdue'   => '喜茶券即将到期，快选一杯喜欢的茶吧'
    ];

    const PATH_GO = [
        'task'      => 1,   // 任务中心
        'buy_card'  => 2,   // 购卡页面
        'star_club' => 3,   // 星球俱乐部页面
        'coupons'   => 4    // 喜茶券列表
    ];

    protected $fillable = ['id', 'user_id', 'title', 'content', 'type', 'tab', 'path_go'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *
     * @param type $user_id
     * @param type $title
     * @param type $content
     * @param type $type
     * @param type $tab
     * @param type $path_go(1跳任务中心,2跳开通会员页面进行续费,3跳转到星球俱乐部,4喜茶券)
     */
    public static function createMessage($user_id, $title, $content, $path_go, $type=0, $tab=0) {
        Message::create([
            'user_id'   => $user_id,
            'title'     => $title,
            'content'   => $content,
            'type'      => $type,
            'tab'       => $tab,
            'path_go'   => $path_go
        ]);
    }

    /** go会员升级提醒 */
    public static function goUpgradeMsg($user_id) {
        self::createMessage($user_id, self::TITLE['go_upgrade'], self::CONTENT['go_upgrade'], self::PATH_GO['task']);
    }

    /** 星球会员升级提醒 */
    public static function starUpgradeMsg($user_id) {
        self::createMessage($user_id, self::TITLE['star_upgrade'], self::CONTENT['star_upgrade'], self::PATH_GO['task']);
    }

    /** 星球会员过期提醒 */
    public static function starOverdueMsg($user_id) {
        self::createMessage($user_id, self::TITLE['star_overdue'], self::CONTENT['star_overdue'], self::PATH_GO['buy_card']);
    }

    /** 获得喜茶券通知 */
    public static function couponsGetMsg($user_id) {
        self::createMessage($user_id, self::TITLE['coupons_get'], self::CONTENT['coupons_get'], self::PATH_GO['coupons']);
    }

    /** 喜茶券退回通知 */
    public static function couponsReturnMsg($user_id) {
        self::createMessage($user_id, self::TITLE['coupons_return'], self::CONTENT['coupons_return'], self::PATH_GO['coupons']);
    }

    /** 喜茶券到期通知 */
    public static function couponsOverdueMsg($user_id) {
        self::createMessage($user_id, self::TITLE['coupons_overdue'], self::CONTENT['coupons_overdue'], self::PATH_GO['coupons']);
    }

}
