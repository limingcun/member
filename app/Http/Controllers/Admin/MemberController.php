<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/3/26
 * Time: 下午2:31
 * desc: 会员控制器
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\CardCodeOrder;
use App\Models\CouponLibrary;
use App\Models\MemberCardRecord;
use App\Models\StarLevel;
use App\Models\VkaRecord;
use App\Transformers\Admin\CouponItemTransformer;
use App\Transformers\Admin\StarCouponScoreTransformer;
use http\Exception;
use Illuminate\Http\Request;
use App\Models\Member;
use DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\MemberScore;
use App\Transformers\Admin\MemberTransformer;
use App\Transformers\Admin\MemberItemTransformer;
use App\Transformers\Admin\MemberCouponTransformer;
use App\Transformers\Admin\MemberPersonScoreTransformer;
use App\Transformers\Admin\CouponPersonLibraryTransformer;
use App\Transformers\Admin\CouponLibraryItemTransformer;
use App\Transformers\Admin\MallOrderItemTransformer;
use App\Http\Repositories\Admin\MemberRepository;
use App\Transformers\Admin\MemberCardOrderTransformer;
use App\Transformers\Admin\MemberExpTransformer;
use IQuery;

class MemberController extends ApiController
{
    protected $redis_path = 'laravel:memberdata:';

    /*
     * /**
     * 会员列表加条件查询
     * @param string $keyword
     * @param int $status
     * @return object
     */
    public function index(Request $request)
    {
        $rps = new MemberRepository();
        $member = $rps->index($request);
        return $this->response->collection($member['data'], new MemberTransformer(), ['pagination' => $member['pagination']]);
    }


    /**
     * 更新会员信息
     * @param Request $request
     * @param int $id user_id
     * @return object
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'birthday' => 'nullable|date',
            'phone' => 'nullable|regex:/^1[3456789][0-9]{9}$/',
//            'usable_score' => 'integer',
            'score_lock' => 'required|integer',
//            'exp' => 'required|integer',
//            'star_exp' => 'required|integer'
        ]);
        $user = User::findOrFail($id);
        $birth = $user->birthday;
        $user->update($request->all('birthday', 'phone'));
        // 生日修改成功 调用
        if ($birth != $request['birthday']) {
            $rps = new MemberRepository();
            $rps->updateBirthday($user->id, $user->birthday);
        }
        $user->members()->update($request->all('score_lock'));
    }

    /**
     * 获取查询会员等级
     */
    public function queryMember(Request $request) {
        $rps = new MemberRepository();
        $res = $rps->queryMember($request);
        return response()->json($res);
    }

    /*
     * 时间范围内新增会员信息
     * @param Request $request
     * @return object
     */
    public function memberNum()
    {
        $rps = new MemberRepository();
        $res = $rps->memberNum();
        return $res;
    }



    /*
     * 用户详情（积分）
     * $id用户id
     */
    public function show($id) {
        $rps = new MemberRepository();
        $member = $rps->show($id);
        return $this->response->item($member);
    }

    /*
     * 星球会员历史记录
     * $id用户id
     */
    public function starList($id) {
        $rps = new MemberRepository();
        $star_data = $rps->starList($id);
        return response()->json($star_data);
    }

    /**
     * 会员go和星球经验值
     * $user_id用户id
     */
    public function memberGoStarExp($user_id) {
        $rps = new MemberRepository();
        $member = $rps->memberGoStarExp($user_id);
        $go_level = $member->level->name;
        $go_level_exp = $member->exp;
        $is_star = Member::isStarMember($member);
        if (!$is_star) {
            if ($member->star_level_id > 0) {
                $star_level = $member->starLevel->name;
                $star_level_exp = $member->star_exp;
                $status = 1;
            } else {
                $star_level = $star_level_exp = '未激活星球会员';
                $status = 2;
            }
        } else {
            $star_level = $member->starLevel->name;
            $star_level_exp = $member->star_exp;
            $status = 0;
        }
        return compact('go_level', 'go_level_exp', 'star_level', 'star_level_exp', 'status');
    }

    /**
     * 会员经验值明细
     * @param type $user_id
     */
    public function memberExp($user_id) {
        $rps = new MemberRepository();
        $member_exps = $rps->memberExp($user_id);
        return $this->response->collection($member_exps, new MemberExpTransformer());
    }

    /*
     * 用户现有权益 （基本上等于是目前可使用的券）
     */
    public function goAndStarRight($user_id) {
        $rps = new MemberRepository();
        $data = $rps->goStar($user_id);
        return response()->json($data);
    }


    /**
     * 个人积分详情页
     * $id用户id
     */
    public function scoreList(Request $request, $id) {
        // 区分来源 type=1 为查看满单赠饮券的订单 否则为普通积分订单
        $type = $request['type'] ?? 0;
        $rps = new MemberRepository();
        if ($type == 1) {
            $score = $rps->starScoreList($id);
            if ($score == false) {
                return response()->json(['code' => 2001, 'msg' => '']);
            }
            return $this->response->collection($score, new StarCouponScoreTransformer());
        } else {
            $score = $rps->scoreList($id);
            return $this->response->collection($score, new MemberPersonScoreTransformer());
        }
    }

    /**
     * 可用优惠券列表
     * @param type $id
     */
    public function usableCouponList(Request $request) {
        $user_id = $request->user_id;
        $rps = new MemberRepository();
        $library = $rps->usableCouponList($user_id);
        return $this->response->collection($library, new CouponPersonLibraryTransformer());
    }

    /**
     * 个人全部优惠券列表
     * @param type $id
     */
    public function allCouponList(Request $request) {
        $user_id = $request->user_id;
        $rps = new MemberRepository();
        $library = $rps->allCouponList($request, $user_id);
        return $this->response->collection($library, new CouponPersonLibraryTransformer());
    }

    /**
     * 优惠券详情
     * @param type $coupon_id优惠券id
     */
    public function couponDetail($coupon_id) {
        $rps = new MemberRepository();
        $tran = new CouponLibraryItemTransformer();
        $library = $rps->couponDetail($coupon_id);
        if ($library->order_id != 0) {
            $order = \App\Models\Order::select('no', 'refund_status', 'paid_at')->find($library->order_id);
            if ($order->refund_status == 'FULL_REFUND') {
                $order->method = 10;
                $order->type_no = 10;
            } else {
                $order->method = 1;
                $order->type_no = 1;
            }
        } else {
            $order = '';
        }
        $coupon_item = $tran->transform($library);
        return compact('coupon_item', 'order');
    }

    /**
     * 星球会员满单赠饮券信息
     */
    public function starCoupon($id)
    {
        $diamond_exp = StarLevel::where('name', '钻石')->select('exp_min')->first();
        // 当前是星球会员 且等级大于等于钻石
        $member = Member::where('user_id', $id)
            ->select(['id', 'member_cup', 'star_level_id'])
            ->where('expire_time', '>=', Carbon::today())
            ->where('star_exp', '>=', $diamond_exp->exp_min)->first();
        if ($member) {
            $star_level_name = $member->starLevel->name ?? '';
            return response()->json([
                'data' => [
                    'star_level_name' => $star_level_name,
                    'enough' => getEnough($star_level_name)['enough'],
                    'amount' => $member->member_cup ?? 0,
                ]
            ]);
        }
        return response()->json(['data' => '']);
    }

    /**
     * 订单详情页
     * method获取积分方法
     * no订单号
     */
    public function orderDetail(Request $request) {
        $method = $request['method'];
        $no = $request->no;
        $type_no = $request->type_no;
        $rps = new MemberRepository();
        if ($method == MemberScore::METHOD['cost'] || $method == MemberScore::METHOD['star_date']) {
            if ($type_no == 1) {
                $res = $rps->orderDetail('no', $no);
                $res = $this->discountPrice($res);
                if ($res) {
                    $res->status_code = 1;
                    $res->status_text = '支付成功';
                }
            } else if ($type_no == 2) {
                $card_order = $rps->cardOrder($no);
                $res = $this->response->item($card_order, new MemberCardOrderTransformer());
            }
        } else if ($method == MemberScore::METHOD['refund']) {
            $res = $rps->orderDetail('no', $no);
            $res = $this->discountPrice($res);
            if ($res) {
                $res->status_code = 0;
                $res->status_text = '已退款';
            }
        } else if ($method == MemberScore::METHOD['change']) {
            $mall_order = $rps->mallOrderDetail($no);
            $res = $this->response->item($mall_order, new MallOrderItemTransformer());
        }
        return $res;
    }

    /**
     * 统计折扣金额
     * $res数组循环
     */
    public function discountPrice($res) {
        $items = $res->items;
        $discountPrice = 0.00;
        if (count($items) > 0) {
            foreach($items as $item) {
                $discountPrice += $item->discount_price;
            }
        }
        $res->discountPrice = number_format($discountPrice, 2);
        return $res;
    }


    /*
     * 会员积分解冻
     */
    public function unLockScore($id)
    {
        $member = Member::where('user_id', $id)->first();
        $res = $member->update(['score_lock' => Member::SCORELOCT['unlock']]);
        if ($res) {
            return success_return();
        }
        return response()->json(['code' => 2001]);
    }

    /*
     * 用户数量和星球会员数量增长点
     * 日增长点(24个点)
     * 周增长点(7个点)
     * 月增长点(30个点)
     * 年增长点(12)个点
     * $t日周月年(0,1,2,3)
     */
    public function memberIncrease(Request $request) {
        $t = $request->t;
        $sdate = $this->redis_path.'_'.$t.Carbon::today()->timestamp;
        $pointArr = IQuery::redisGet($sdate);
        if (!$pointArr) {
            $rps = new MemberRepository();
            switch($t) {
                case 0:
                    $pointArr = $rps->dateIncrease(24);
                    break;
                case 1:
                case 2:
                    $pointArr = $rps->weekOrMonthIncrease(Carbon::yesterday()->format('d'), $t);
                    break;
                case 3:
                    $pointArr = $rps->yearIncrease(Carbon::today()->format('m'));
                    break;
                default:
                    $pointArr = '';
                    break;
            }
            IQuery::redisSet($sdate, $pointArr, 3600 * 24);
        }
        return response()->json($pointArr);
    }

    /**
     * 付费数据与迁移数据
     */
    public function memberData()
    {
        $rps = new MemberRepository();
        return $this->response->json($rps->starMemberData());
    }
}
