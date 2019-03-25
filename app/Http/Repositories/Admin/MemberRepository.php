<?php

namespace App\Http\Repositories\Admin;

use App\Models\CardCodeOrder;
use App\Models\Coupon;
use App\Models\GiftRecord;
use App\Models\VkaRecord;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Http\Repositories\BaseRepository;
use App\Models\MemberCardRecord;
use Carbon\Carbon;
use App\Models\StarLevel;
use App\Models\Level;
use App\Models\Order;
use App\Models\MallOrder;
use App\Models\MemberScore;
use App\Models\MemberExp;
use App\Models\CouponLibrary;
use DB;
use IQuery;

class MemberRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = new Member();
    }

    public function index(Request $request)
    {
        $page_size = $request->page_size ?? config('app.page'); //页码
        $page = $request->page ?? 1; //当前页
        $total = $this->querySearch(new Member(), $request)->count();
        $per = ($page - 1) * $page_size;
        $members = Member::offset($per)->limit($page_size);
        $members = $this->querySearch($members, $request);
        $members = $members->get();
        return $this->burster($members, $page, $page_size, $total);
    }

    public function querySearch($member, $request) {
        $member = $member
            ->with([
                'user',
                'level',
                'starLevel',
                'storage'
            ])->when($request->keyword, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->whereIn('user_id', function($query) use ($value) {
                        $query->select('id')->from('users')->where('phone', $value)->orWhere('id', $value);
                    });
                });
            })->when($request->sex, function ($query, $value) {
                if ($value != 'unknow') {
                    $query->where('sex', $value);
                } else {
                    $query->whereIn('sex', ['unknow', '']);
                }
            });
        if ($request->member_type != '') {
            $member_type = $request->member_type;
            if ($member_type == Member::MEMBERTYPE['go']) {
                $member = $member->whereNotIn('id', function($query) {
                    $query->select('id')->from('members')->whereNotNull('expire_time')->whereDate('expire_time', '>=', Carbon::today());
                });
            } else if ($member_type == Member::MEMBERTYPE['star']) {
                $member = $member->whereDate('expire_time', '>=', Carbon::today());
            } else if ($member_type == Member::MEMBERTYPE['vka']) {
                $member = $member->where('member_type', Member::MEMBERTYPE['vka'])->whereDate('expire_time', '>=', Carbon::today());
            }
        }
        if ($request->storage_money_min != '') {
            $storage_money_min = $request->storage_money_min;
            $member = $member->whereIn('user_id', function($query) use($storage_money_min) {
                $query->select('user_id')->from('cash_storages')->whereNull('deleted_at')->where('free_money', '>=', $storage_money_min);
            });
        }
        if ($request->storage_money_max != '') {
            $storage_money_max = $request->storage_money_max;
            $member = $member->whereIn('user_id', function($query) use($storage_money_max)  {
                $query->select('user_id')->from('cash_storages')->whereNull('deleted_at')->where('free_money', '<=', $storage_money_max);
            });
        }
        $member = $this->whereQuery($member, [
            'score_min' => 'usable_score',
            'score_max' => 'usable_score',
            'exp_min' => 'exp',
            'exp_max' => 'exp',
            'star_exp_min' => 'star_exp',
            'star_exp_max' => 'star_exp',
            'register_start' => 'created_at',
            'register_end' => 'created_at',
            'star_expire_start' => 'expire_time',
            'star_expire_end' => 'expire_time',
            'level_id' => 'level_id',
            'star_level_id' => 'star_level_id'
        ], $request);
        return $member;
    }

    public function memberNum()
    {
        $sql = "SELECT count(id) total_member FROM members";
        $res = DB::select($sql);
        $total_member = 0;
        foreach($res as $r) {
            $total_member = $r->total_member;
        }
        return response(compact('total_member'));
    }

    public function show($id)
    {
        $member = Member::with('user', 'level', 'starLevel')->where('user_id', $id)->first();
        return $member;
    }

    public function starList($id)
    {
        $star_data = MemberCardRecord::where('user_id', $id)->where('status', MemberCardRecord::STATUS['is_pay'])->orderBy('id', 'desc')->paginate(4);
        foreach($star_data as $star) {
            $star->method = 1;
            $star->type_no = 2;
        }
        return $star_data;
    }

    public function getCouponArr($coupon_libs, $go_flags, $flag_id)
    {
        $arr = [];
        foreach ($go_flags as $go_flag) {
            $coupon_id = $flag_id[Coupon::FLAG[$go_flag]];
            if ($coupon_libs->where('coupon_id', $coupon_id)->count('id')) {
                $name = $coupon_libs->where('coupon_id', $coupon_id)->first();
                $count =$coupon_libs->where('coupon_id', $coupon_id)->count('id');
                $arr[] = $name->name . ' * ' . $count;
            }
        }
        return $arr;
    }

    public function goStar($user_id)
    {
        IQuery::redisDelete('flag_coupon_id');
        $flag_id = IQuery::redisGet('flag_coupon_id');
        // 将coupon_id与flag对应，并缓存
        if (!$flag_id) {
            $coupons = Coupon::where('flag', '>=', 10)->select(['id', 'flag'])->get();
            foreach ($coupons as $coupon) {
                $flag_id[$coupon->flag] = $coupon->id;
            }
            IQuery::redisSet('flag_coupon_id', $flag_id, 3600 * 24);
        }
        // 查到用户身上所有的特殊券
        $coupon_libs = CouponLibrary::where('user_id', $user_id)->whereIn('coupon_id', $flag_id)
            ->where('status', CouponLibrary::STATUS['surplus'])->where('period_end', '>=', Carbon::today())
            ->get();
        // 分别装到不同的分类里面
        // go 会员
        $go_flags = ['cash_120-5', 'cash_110-10', 'cash_110-15', 'cash_100-15', 'cash_100-20', 'cash_100-25',
            'buy_fee_6-1', 'buy_fee_5-1', 'buy_fee_4-1', 'buy_fee_3-1', 'buy_fee_2-1', 'buy_fee_1-1'
        ];
        $go = $this->getCouponArr($coupon_libs, $go_flags, $flag_id);
        // 星球会员福利
        $star_flags = ['cash_150-5' , 'cash_150-10', 'cash_150-15', 'cash_150-20', 'cash_150-25', 'cash_150-30',
            'fee_star_20', 'fee_star_10', 'fee_star_5', 'fee_star_birthday', 'fee_star_anniversary', 'fee_star_prime_day',
            'discount_star_month', 'queue_star_month', 'buy_fee_star_3-1', 'buy_fee_star_2-1', 'buy_fee_star_1-1'];
        $star = $this->getCouponArr($coupon_libs, $star_flags, $flag_id);

        // 星球会员特权
        $member = Member::where('user_id', $user_id)->select(['id', 'star_level_id', 'expire_time'])->first();
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->endOfDay();
        $star_level_id = $member->star_level_id;
        if ($star_level_id == 1) {
            $plg[] = '每月12日会员日额外奉送5%积分';
            $plg[] = '积分和经验值涨速翻倍';
        } else if ($star_level_id == 2) {
            $plg[] = '每月12日会员日额外奉送10%积分';
            $plg[] = '积分和经验值涨速翻倍';
            $plg[] = '外卖配送运费9折';
        } else if ($star_level_id == 3) {
            $plg[] = '每月12日会员日额外奉送15%积分';
            $plg[] = '积分和经验值涨速翻倍';
            $plg[] = '外卖配送运费7折';
        } else if ($star_level_id == 4) {
            $plg[] = '每月12日会员日额外奉送20%积分';
            $plg[] = '积分和经验值涨速翻倍';
            $plg[] = '外卖配送运费5折';
        } else if ($star_level_id == 5) {
            $plg[] = '每月12日会员日额外奉送25%积分';
            $plg[] = '积分和经验值涨速翻倍';
            $plg[] = '外卖配送运费3折';
            $plg[] = '喜茶会员活动优先报名权';
            $plg[] = '商城指定商品兑换';
        } else if ($star_level_id == 6) {
            $plg[] = '每月12日会员日额外奉送30%积分';
            $plg[] = '积分和经验值涨速翻倍';
            $plg[] = '外卖配送0运费';
            $plg[] = '喜茶会员活动优先报名权（优先参与）';
            $plg[] = '商城指定商品兑换';
        } else {
            $plg = [];
        }
        $is_star = $expire_time > Carbon::now() ? true : false;
        // 星球会员开卡奖励 首充奖励 Vka迁移券 星球会员升级券
        $others = [
            'buy_fee_card_2-1', 'buy_fee_card_1-1', 'fee_card_take_fee','discount_card', 'queue_card',
            'buy_fee_first_1-1', 'cash_star_first',
            'vka_fee','vka_buyfee',
        ];
        if ($is_star) {
            $others[] = 'fee_star_update';
        }
        $other = $this->getCouponArr($coupon_libs, $others, $flag_id);
        return ['is_star' => $is_star, 'go' => $go, 'star' => $star, 'other' => $other, 'plg' => $plg];
    }


    /*
     * 日用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     */
    public function dateIncrease($num)
    {
        $randArr = [];
        for ($i = 1; $i <= 24; $i++) {
            if ($i < 10) {
                $c = '0' . $i;
            } else {
                $c = $i;
            }
            $randArr[] = $c . ':00';
        }
        $start = Carbon::yesterday()->startOfDay();
        $end = Carbon::yesterday()->endOfDay();
        $uarr = [];
        $sql = "SELECT DATE_FORMAT(u.created_at,'%Y-%m-%d %H') as time, count(1) as num, u.created_at as c_time from users as u where u.created_at BETWEEN '" .$start. "' and '" .$end. "' GROUP BY time";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if ($r->c_time >= $start && $r->c_time < $end) {
                $timeArr = explode(' ', $r->time);
                $uarr[intval($timeArr[1])] = $r->num;
            }
        }
        $arr = $this->forUserStarArr($uarr, 0, $num - 1, $randArr);
        return $arr;
    }

    /*
     * 周或月用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     * $t是周或月类型
     */
    public function weekOrMonthIncrease($num, $t)
    {
        $randArr = [];
        if ($t == 1) {
            $start = Carbon::today()->subWeek(1);
            $end = Carbon::yesterday()->endOfDay();
            $s = $start->format('d');
            for ($i = 0; $i < 7; $i++) {
                $randArr[] = Carbon::today()->subWeek(1)->addDay($i)->format('Y-m-d');
            }
        } else if ($t == 2) {
            $start = Carbon::today()->subMonth(1);
            $end = Carbon::yesterday()->endOfDay();
            $s = $start->format('d');
            $ts = Carbon::now()->subMonth()->lastOfMonth()->format('d') - $s + Carbon::today()->format('d');
            for ($i = 0; $i < $ts; $i++) {
                $randArr[] = Carbon::today()->subMonth(1)->addDay($i)->format('Y-m-d');
            }
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(u.created_at,'%Y-%m-%d')) as time, count(1) as num from users as u where u.created_at BETWEEN '" .$start. "' and '" .$end. "' GROUP BY time";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if (Carbon::parse($r->time)->timestamp >= Carbon::parse($start)->timestamp && Carbon::parse($r->time)->timestamp < Carbon::parse($end)->timestamp) {
                $uarr[$r->time] = $r->num;
            }
        }
        $arr = $this->forWeekMonthYearArr($uarr, $randArr);
        return $arr;
    }

    /*
     * 年用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     */
    public function yearIncrease($num)
    {
        $randArr = [];
        $start = Carbon::today()->subYear(1);
        $end = Carbon::today()->subMonth(1);
        for ($i = 0; $i < 12; $i++) {
            $randArr[] = Carbon::today()->subYear(1)->addMonth($i)->format('Y-m');
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(u.created_at,'%Y-%m')) as time, count(1) as num from users as u GROUP BY MONTH(u.created_at)";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if ($r->time >= $start && $r->time < $end) {
                $uarr[$r->time] = $r->num;
            }
        }
        $arr = $this->forWeekMonthYearArr($uarr, $randArr);
        return $arr;
    }

    /*
     * 日循环传值
     * $uarr用户增长数组
     * $start开始循环值
     * $end结束循环值
     * $randArr时间范围
     */
    public function forUserStarArr($uarr, $start, $end, $randArr)
    {
        $countArr = [];
        for ($i = $start; $i <= $end; $i++) {
            if (array_key_exists($i, $uarr)) {
                $countArr[] = $uarr[$i];
            } else {
                $countArr[] = 0;
            }
        }
        return ['time_arr' => $randArr, 'count_arr' => $countArr, 'increase_num' => array_sum($countArr)];
    }

    /*
     * 周月循环传值
     * $uarr用户增长数组
     * $randArr时间范围
     */
    public function forWeekMonthYearArr($uarr, $randArr)
    {
        $countArr = [];
        for ($i = 0; $i < count($randArr); $i++) {
            if (array_key_exists($randArr[$i], $uarr)) {
                $countArr[] = $uarr[$randArr[$i]];
            } else {
                $countArr[] = 0;
            }
        }
        return ['time_arr' => $randArr, 'count_arr' => $countArr, 'increase_num' => array_sum($countArr)];
    }

    /*
     * 查询go会员,星球会员各等级
     */
    public function queryMember(Request $request)
    {
        $type = $request->type;
        if ($type == 'go') {
            $res = Level::select('id', 'name')->get();
        } else if ($type = 'star') {
            $res = StarLevel::select('id', 'name')->get();
        } else {
            $res = '';
        }
        return $res;
    }

    /*
     * 个人积分列表
     * $id会员id
     */
    public function scoreList($id)
    {
        $page = request('page_size') ?? config('app.page');
        $score = MemberScore::with('source')->where('user_id', $id)->orderBy('id', 'desc')->paginate($page);
        return $score;
    }

    /*
     * 个人可用优惠券
     */
    public function usableCouponList($user_id)
    {
        $library = CouponLibrary::with('coupon', 'grand')->where('user_id', $user_id)->where('status', CouponLibrary::STATUS['surplus']);
        return $library->orderBy('id', 'desc')->paginate(4);
    }

    /**
     * 个人全部优惠券
     * @param Request $request
     * @param type $id
     */
    public function allCouponList(Request $request, $user_id)
    {
        $library = CouponLibrary::with('coupon', 'grand')->where('user_id', $user_id)
            ->when($request->keyword, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('name', $value)->orWhere('code_id', $value);
                });
            });
        $library = $this->whereQuery($library, [
            'status' => 'status',
            'pick_start' => 'created_at',
            'pick_end' => 'created_at'
        ], $request);
        return $library->orderBy('id', 'desc')->paginate(4);
    }

    /**
     * 优惠券详情
     * @param type $coupon_id优惠券id
     */
    public function couponDetail($coupon_id)
    {
        $library = CouponLibrary::findOrFail($coupon_id);
        return $library;
    }

    /**
     * 优惠订单详情页
     * name
     */
    public function orderDetail($name, $value)
    {
        $field = ['orders.id', 'orders.no', 'orders.paid_at', 'shops.name as shop_name', 'shops.contact_phone as shop_phone', 'shops.address as shop_address', 'orders.remarks',
            'users.name as user_name', 'users.phone as user_phone', 'order_addresses.address as user_address', 'orders.delivery_fee', 'orders.box_fee', 'orders.is_takeaway',
            'orders.total_fee', 'orders.discount_fee', 'orders.payment', 'orders.trade_type', 'orders.paid_type', 'orders.refund_status'];
        $order = Order::leftJoin('users', 'users.id', 'orders.user_id')
            ->leftJoin('shops', 'shops.id', 'orders.shop_id')
            ->leftJoin('order_deliveries', 'order_deliveries.order_id', 'orders.id')
            ->leftJoin('refund_orders', 'refund_orders.order_id', 'orders.id')
            ->leftJoin('order_addresses', 'orders.id', 'order_addresses.order_id')
            ->whereNull('users.deleted_at')
            ->whereNull('shops.deleted_at')
            ->whereNull('refund_orders.deleted_at')
            ->whereNotNull('orders.paid_at');
        if ($name == 'no') {
            $order = $order->where('orders.no', $value);
        } else {
            $order = $order->where('orders.id', $value);
        }
        $order = $order->first($field);
        if ($order) {
            $items = $this->orderItem($order->id);
            foreach ($items as $item) {
                $attributes = $this->orderItemAttribute($item->id);
                $materials = $this->orderItemMaterial($item->id);
                $specifications = $this->orderItemSpecification($item->id);
                $item->attributes = $attributes;
                $item->materials = $materials;
                $item->specifications = $specifications;
            }
            $order->items = $items;
        }
        return $order;
    }

    /**
     * 订单项数据
     * @param type $order_id
     * @return type
     */
    public function orderItem($order_id)
    {
        $items = DB::table('order_items')
            ->leftJoin('activities', 'order_items.activity_id', 'activities.id')
            ->whereNull('order_items.deleted_at')
            ->where('order_items.order_id', $order_id)
            ->select('order_items.id', 'order_items.name', 'order_items.product_id', 'order_items.price', 'order_items.quantity',
                'order_items.discount_price', 'order_items.total_fee', 'activities.id as activity_id', 'activities.activity_name')
            ->get();
        return $items;
    }

    /**
     * 获取订单属性项
     * @param type $order_item_id
     */
    public function orderItemAttribute($order_item_id)
    {
        $attributes = DB::table('order_item_attributes')->where('order_item_id', $order_item_id)
            ->select('id', 'name', 'value')
            ->get();
        return $attributes;
    }

    /**
     * 获取订单加料项
     * @param type $order_item_id
     */
    public function orderItemMaterial($order_item_id)
    {
        $materials = DB::table('order_item_materials')->where('order_item_id', $order_item_id)
            ->select('id', 'name', 'price')
            ->get();
        return $materials;
    }

    /**
     * 获取订单规格项
     * @param type $order_item_id
     */
    public function orderItemSpecification($order_item_id)
    {
        $specifications = DB::table('order_item_specifications')->where('order_item_id', $order_item_id)
            ->select('id', 'name', 'value')
            ->get();
        return $specifications;
    }

    /**
     * 个人积分商城兑换详情页
     */
    public function mallOrderDetail($no)
    {
        $mall_order = MallOrder::with('item', 'item.product', 'item.source')->where('no', $no)->first();
        return $mall_order;
    }

    /**
     * 购卡订单
     * @param type $no
     * @return type
     */
    public function cardOrder($no) {
        $member_card = MemberCardRecord::where('order_no', $no)->first();
        return $member_card;
    }

    /**
     * 后台更新用户生日后调用  判断是否要给用户发放新的生日券
     */
    public function updateBirthday($user_id, $birth)
    {
        $birth = strtotime($birth);
        $coupon_id = Coupon::where('flag', Coupon::FLAG['fee_star_birthday'])->value('id');
        $birth_coupon = CouponLibrary::where('user_id', $user_id)->where('coupon_id', $coupon_id)
            ->whereYear('period_start', date('Y'))->first();
        if ($birth_coupon) {
            // 今年存在生日券
            if ($birth_coupon->status != CouponLibrary::STATUS['used']) {
                // 没有使用过
                $birth_coupon->delete();    //删除
                $this->sendBirthCoupon($user_id, $birth);
            }
        } else {
            $this->sendBirthCoupon($user_id, $birth);
        }
    }

    // 发券
    public function sendBirthCoupon($user_id, $birth)
    {
        $member = Member::where('user_id', $user_id)->where('expire_time', '>=', date('Y-m-d'))->first();
        if ($member) {
            // 是星球会员
            // 生日如果在本月 还没过 就发一张券
            if (date('m') == date('m', $birth) && date('d', $birth) >= date('d')) {
                createCoupon('fee_star_birthday', $user_id, 1,
                    Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->endOfDay(),
                    Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->startOfDay());
            }
        }
    }

    // 星球会员付费数据与迁移数据
    public function starMemberData() {
        // 星球会员付费总用户数
        $star_amount = MemberCardRecord::where('status', 1)
            ->where('paid_type', 1)
            ->where('card_no', 0)
            ->distinct()->count('user_id');
        // 星球会员兑换总用户数
        $exchange_amount = MemberCardRecord::where('status', 1)
            ->where('user_id', '!=', 0)
            ->where('paid_type', 4)     // paid = 4 为兑换码会员卡订单
            ->whereNotNull('paid_at')
            ->distinct()->count('user_id');
        // vka迁移会员用户数
        $vka_amount = VkaRecord::where('status', 1)
            ->distinct()->count('user_id');
        // 星球会员卡总收入 购卡收入
        $pay_amount = MemberCardRecord::where('paid_type', 1)
            ->where('status', 1)
            ->sum('price');
        // 兑换码收入
        $code_orders = CardCodeOrder::select(['id', 'price', 'count'])->get();
        $code_pay = 0;
        foreach ($code_orders as $code_order) {
            $code_pay += $code_order->price * $code_order->count;
        }
        // 分组查询年卡、半年卡、季卡、体验卡的记录
        $records = DB::table('member_card_records')
            ->select(DB::raw('card_type, count(*) as count'))
            ->where('user_id', '!=', 0)
            ->where('card_no', 0)
            ->where('status', 1)
            ->where('card_type', '!=', 6)
            ->groupBy('card_type')->get();
        $recordCount = array_sum($records->pluck('count')->toArray());
        $memberCardRecord = array();
        foreach ($records as $record) {
            $memberCardRecord[] = [
                'card_type' => $record->card_type,
                'count'     => $record->count,
                'percent'   => round($record->count/$recordCount, 2)
            ];
        }

        return [
            'star_amount' => $star_amount,
            'exchange_amount' => $exchange_amount,
            'vka_amount' => $vka_amount,
            'pay_amount' => round($pay_amount,2),
            'code_total_prices' => round($code_pay,2),
            'member_card_record' => $memberCardRecord
        ];
    }
}
