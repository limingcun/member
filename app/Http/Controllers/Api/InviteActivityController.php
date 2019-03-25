<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\ActivityAddress;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Member;
use App\Models\MemberCardRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use IQuery;
use DB;

class InviteActivityController extends ApiController
{
    // 9.9邀请活动

    /**
     * 首页接口
     */
    public function index()
    {
        $user_id = $this->user()->id;
        $amount = $this->invitationTotal($user_id); // 邀请总数
        if ($amount >= 5) {
            $invite_coupon = $this->inviteCoupon($user_id); // 领取过除首张券的买一赠一券数量
            $invite_first_coupon = $this->inviteFirstCoupon($user_id);  // 是否领取过首张5个邀请兑换的买一赠一券
            $usable_amount = $amount - ($invite_first_coupon ? 1 : 0) * 5 - $invite_coupon * 10;    // 剩余可用于兑换券的邀请数
        } else {
            $invite_coupon = 0;
            $invite_first_coupon = 0;
            $usable_amount = $amount;
        }
        $address = ActivityAddress::where('user_id', $user_id)->where('status', 1)->where('type', 1)->exists();   // 是否填写了收货地址
        return $this->response->json([
            'code' => 0,
            'data' => [
                'amount' => $amount,
                'address' => $address,
                'usable_amount' => $usable_amount,
                'invite_coupon_amount' => $invite_coupon,
                'invite_first_coupon' => $invite_first_coupon,
            ]
        ]);
    }

    /**
     * 判断当前用户是否参与过邀请活动
     */
    public function isJoin()
    {
        $user_id = $this->user()->id;
        $status = false;
        // 只需要判断用户是否成为过星球会员 即start_time不为空
        $member = Member::select('id')
            ->whereNotNull('star_time')
            ->where('user_id', $user_id)
            ->first();
        if ($member) {
            $status = true;
        }
        return $this->response->json(['id' => $user_id,'status' => $status]);
    }

    /**
     * 活动是否过期
     */
    public function inviteActivityStatus()
    {
        $code = inviteActivityStatus();
        if ($code != 1) {
            return $this->response->json(['code' => 2001, 'msg' => '活动不在有效期内']);
        } else {
            return $this->response->json(['code' => 0, 'msg' => '']);
        }
    }

    /**
     * 领券接口
     */
    public function getCoupon(Request $request)
    {
//        if (inviteActivityStatus() != 0) {
//            return $this->response->json(['code' => 2001, 'msg' => '活动不在有效期内']);
//        }
        $type = $request['type'] ?? 0;
        if (!$type) {
            return $this->response->json(['code' => 2002, 'msg' => '请求参数错误']);
        }
        $user_id = $this->user()->id;
        $flag = IQuery::redisGet('invite_coupon_'.$user_id);
        // 处理时加锁
        if ($flag) {
            return $this->response->json(['code' => 2003, 'msg' => '正在领取中...']);
        }
        IQuery::redisSet('invite_coupon'.$user_id, 1,60);

        $amount = $this->invitationTotal($user_id); // 邀请总数
        $invite_coupon = $this->inviteCoupon($user_id); // 领取过除首张券的买一赠一券数量
        $invite_first_coupon = $this->inviteFirstCoupon($user_id);  // 是否领取过首张5个邀请兑换的买一赠一券
        $usable_amount = $amount - ($invite_first_coupon ? 1 : 0) * 5 - $invite_coupon * 10;    // 剩余可用于兑换券的邀请数

        if (1 == $type && $usable_amount >= 5) {
            if ($invite_first_coupon) {
                IQuery::redisDelete('invite_coupon_'.$user_id);
                return $this->response->json(['code' => 2004, 'msg' => '首次奖励只能领一次哦']);
            }
            // 领取首张5个邀请兑换的买一赠一券
            $this->getInviteFirstCoupon($user_id);
        } else if (2 == $type && $usable_amount >= 10) {
            $this->getInviteCoupon($user_id);
        } else {
            IQuery::redisDelete('invite_coupon_'.$user_id);
            return $this->response->json(['code' => 2005, 'msg' => '领取失败']);
        }
        IQuery::redisDelete('invite_coupon_'.$user_id);
        return $this->response->json(['code' => 0, 'msg' => '']);
    }

    /**
     * 收货地址
     */
    public function address(Request $request)
    {
        $user_id = $this->user()->id;
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'name' => 'required',
                'phone' => 'required',
                'address' => 'required'
            ]);
            $flag = IQuery::redisGet('invite_address_'.$user_id);
            // 处理时加锁
            if ($flag) {
                return $this->response->json(['code' => 2003, 'msg' => '正在处理中...']);
            }
            IQuery::redisSet('invite_address_'.$user_id, 1,60);
            $amount = $this->invitationTotal($user_id); // 邀请总数
            if ($amount < 25) {
                return $this->response->json(['code' => 2002, 'msg' => '处理失败']);
            }
            // 存在收货地址时，不能新增和修改收货地址
            $is_exists = ActivityAddress::where('user_id', $user_id)->where('status', 1)->where('type', 1)->exists();
            if (!$is_exists) {
                ActivityAddress::create([
                    'user_id' => $this->user()->id,
                    'name' => $request['name'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'type' => 1,
                    'status' => 1
                ]);
                IQuery::redisDelete('invite_address_'.$user_id);
                return $this->response->json(['code' => 0, 'msg' => '']);
            } else {
                IQuery::redisDelete('invite_address_'.$user_id);
                return $this->response->json(['code' => 2001, 'msg' => '地址只能提交一次哦']);
            }
        } elseif ($request->isMethod('get')) {
            $address = ActivityAddress::select(['user_id', 'name', 'phone', 'address'])
                ->where('user_id', $user_id)
                ->where('status', 1)
                ->where('type', 1)->first();
            return $this->response->json(['data' => $address]);
        }
        abort(404, '请求错误');
    }


    /**
     * 查看用户邀请列表
     */
    public function invitationList(Request $request)
    {
        $page = $request['page'] ?? 1;
        $page_size = $request['page_size'] ?? 10;
        if ($page_size <= 0) {
            abort(400, '每页数量不能为0');
        }
        $user_id = $this->user()->id;
        $total = DB::table('member_card_records')
            ->where('inviter_id', $user_id)
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->distinct()->count('user_id');
        $list = DB::table('member_card_records as m')
            ->distinct()->select(['m.user_id', 'u.name', 'u.image_url'])
            ->leftJoin('users as u', 'm.user_id', 'u.id')
            ->where('inviter_id', $user_id)
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->offset(($page-1) * $page_size)->limit($page_size)->get();
        return $this->response->json([
            'data' => $list,
            'total' => $total,
            'total_pages' => ceil($total/$page_size),
            'count' => (int)$page_size,
            'current_page' => (int)$page
        ]);
    }

    /**
     * 查看用户邀请总数
     */
    public function invitationTotal($user_id)
    {
        $count = MemberCardRecord::where('inviter_id', $user_id)
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['vka'])
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->distinct()->count('user_id');
        return $count;
    }

    /**
     * 查看用户领取的买一赠一数量 是否领取过满五人领取一张赠饮券
     */
    public function inviteFirstCoupon($user_id)
    {
        $coupon_id = Coupon::where('flag', Coupon::FLAG['invite_5_buy_fee'])->value('id');
        $is_exists = CouponLibrary::where('user_id', $user_id)->where('coupon_id', $coupon_id)->exists();
        return $is_exists;
    }

    /**
     * 查看用户已领取满10人买一赠一券数量
     */
    public function inviteCoupon($user_id)
    {
        $coupon_id = Coupon::where('flag', Coupon::FLAG['invite_10_buy_fee'])->value('id');
        $amount = CouponLibrary::where('user_id', $user_id)->where('coupon_id', $coupon_id)->count();
        return $amount;
    }

    /**
     * 领取满五人 买一赠一券
     */
    public function getInviteFirstCoupon($user_id)
    {
        createCoupon('invite_5_buy_fee', $user_id, 1);
    }

    /**
     * 领取满10人 买一赠一券
     */
    public function getInviteCoupon($user_id)
    {
        createCoupon('invite_10_buy_fee', $user_id, 1);
    }
}
