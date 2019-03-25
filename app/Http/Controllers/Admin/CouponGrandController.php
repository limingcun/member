<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/9/7
 * Time: 上午9:22
 * desc: 优惠券发券控制器
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\GrandRequest;
use Illuminate\Support\Facades\Artisan;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponGrand;
use App\Models\CouponLibrary;
use App\Models\User;
use IQuery;
use Log;
use DB;
use Maatwebsite\Excel\Excel;
use Carbon\Carbon;
use App\Models\MemberScore;
use App\Transformers\Admin\CouponGrandItemTransformer;
use App\Services\QrCode;
use App\Models\CouponCodeRecord;

class CouponGrandController extends ApiController
{
    // redis存储路径
    const REDIS_PATH = 'laravel:coupon:';
    // redis针对excel表保存的临时数据
    const REDIS_TIME = 'laravel:time:';

    /*
     * 4001操作失败,
     * 4002发券数量大于模板数量
     */
    const CODE = [
        'error' => 4001,
        'limit' => 4002,
        'is_used' => 4003
    ];
    /*
     * 前端传来喜茶券状态
     * usable可使用
     * expire已过期
     */
    const COUPONSTATUS = [
        'usable' => 1,
        'expire' => 2
    ];
    
    const POLICY = [
        CashCouponPolicy::class,
        FeeCouponPolicy::class,
        BuyFeeCouponPolicy::class,
        DiscountCouponPolicy::class,
        QueueCouponPolicy::class
    ];

    /*
     * 优惠券记录列表
     * page_size页码
     * keyword关键字
     * status状态
     * scence使用场景
     * start创建开始时间
     * end创建结束时间
     */
    public function index(Request $request)
    {
        $page = $request->page_size != '' ? $request->page_size : config('app.page');
        $grands = CouponGrand::with('coupon', 'admin', 'library', 'grandNum')
            ->when($request->keyword, function($query, $value) {
                 $query->where(function ($query) use ($value){
                     $query->where('name', 'like', '%' . $value . '%')->orWhere('no', 'like', '%' . $value . '%');
                 });
            })->when($request->start, function($query, $value) {
                 $query->whereDate('created_at', '>=', $value);
            })->when($request->end, function($query, $value) {
                 $query->whereDate('created_at', '<=', $value);
            });
        if ($request->status != -1 && $request->status != '') {
            $grands = $grands->where('status', $request->status);
        }
        if ($request->scence != 0 && $request->scence != '') {
            $grands = $grands->where('scence', $request->scence);
        }
        return $this->response->collection($grands->orderBy('id', 'desc')->paginate($page));
    }

    /*
     * 优惠券发放
     * name优惠券名称
     * coupon_id优惠券id
     * grand_type发券类型 1为立即发券 2为指定时间发券
     * grand_time 发券时间
     * scence 0代表线上,1代表线下
     * range_type发券范围0为全部用户，1为指定用户，2位excel导入用户
     */
    public function store(GrandRequest $request)
    {
        //判断发放数量是否大于库存数量
        $num = $this->grandLastStore($request);
        if (!$num) {
            return response()->json(['code' => self::CODE['limit']]);
        }
        if (Coupon::where('flag', '!=', 0)->where('id', $request->coupon_id)->first()) {
            return response()->json(['code' => self::CODE['is_used'], 'msg' => '该模板已被使用']);
        }
        try {
            DB::beginTransaction();
            //保存发券
            $grand = CouponGrand::create(array_merge($request->all('name', 'coupon_id', 'grand_type', 'scence', 'range_type'), [
                'no' => create_no('VN'),
                'amount' => $request->amount ?? null,
                'count' => $request->count ?? null,
                'range_msg' => $request->range_msg ?? null,
                'admin_id' => auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id,
                'grand_time' => $request->grand_time ?? Carbon::now(),
                'chanel_type' => $request->chanel_type ?? 0,
                'period_type' => $request->period_type ?? 0,
                'period_start' => $request->period_start ?? null,
                'period_end' => $request->period_end ?? null,
                'period_day' => $request->period_day ?? 0,
                'unit_time' => $request->unit_time ?? 0
            ]));
            if (!$this->saveUserId($request, $grand->id)) {
                DB::rollback();
                Log::info('coupon_grand_error', ['redis_error']);
                return response()->json(['code' => self::CODE['error']]);
            }
            //立即发券
            if ($request->grand_type == CouponGrand::GRANDTYPE['once']) {
                Artisan::call('set:coupon', ['grand_id' => $grand->id]);
            } else {
                if (Carbon::parse($grand->grand_time)->timestamp <= Carbon::now()->timestamp) {
                    Artisan::call('set:coupon', ['grand_id' => $grand->id]);
                }
            }
            //更新喜茶券模板标志
            if ($num > 0) {
                $coupon = Coupon::findOrFail($request->coupon_id);
                $coupon->update([
                    'flag' => Coupon::FLAG['coupon'],
                    'status' => Coupon::STATUS['used']
                ]);
            }
            
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('coupon_grand_error', [$e]);
            return response()->json(['code' => self::CODE['error']]);
        }
    }
    
    /*
     * 判断发放数量是否大于模板库存数量
     * $request
     * @return 返回模板券减去库存
     */
    public function grandLastStore(Request $request) {
        $scence = $request->scence;
        $range = $request->range_type;
        $coupon = Coupon::find($request->coupon_id);
        switch($scence) {
            case CouponGrand::SCENCE['line']:
                if ($range == CouponGrand::RANGETYPE['all']) {
                    $user_count = User::count();
                } else if ($range == CouponGrand::RANGETYPE['spec']) {
                    $user_count = count(explode(';', $request->user_ids));
                } else {
                    $user_count = count(IQuery::redisGet($request->redis_path));
                }
                $num = $user_count * $request->count;
            break;
            case CouponGrand::SCENCE['change']:
                $num = $request->amount;
            break;
            default:
                $num = $request->amount * $request->count;
            break;
        }
        if ($coupon->count < $num) {
            return false;
        }
        return $num;
    }

    /*
     * 保存发放用户数据
     */
    public function saveUserId(Request $request, $grand_id)
    {
        if (!$request->scence) {  //0为线上，1为线下
            switch ($request->range_type) { //1为输入框指定用户，2代表excel表导入指定用户
                case CouponGrand::RANGETYPE['spec']:
                    //指定用户发放，输入框输入id,格式（1;2;3）
                    $user_ids = $request->user_ids;
                    $this->checkUserId($user_ids, $grand_id); //用户id去重和去除不存在
                    return true;
                case CouponGrand::RANGETYPE['excel']:
                    if (!$this->changeRedisPath($request->redis_path, $grand_id)) { //redis从临时路径转移到定时redis
                        return false;
                    }
                    return true;
                default:
                    return true;
            }
        }
        return true;
    }

    /*
     * 编辑与套用显示数据共用
     */
    public function edit($id)
    {
        $grand = CouponGrand::with(['coupon' => function($query) {
            $query->select('id', 'policy', 'name', 'count as store', 'no', 'period_type');
        }])->findOrFail($id);
        if ($grand->range_type) {
            if ($grand->status == CouponGrand::GRANDSTATUS['finish']) {
                $user = array_unique($grand->library()->pluck('user_id')->toArray());
            } else {
                $user = IQuery::redisGet(self::REDIS_PATH . $grand->id);
            }
        }
        $policy = $grand->coupon->policy;
        switch($policy) {
            case self::POLICY[0]:
                $grand->policy = 0;
                break;
            case self::POLICY[1]:
                $grand->policy = 1;
                break;
            case self::POLICY[2]:
                $grand->policy = 2;
                break;
            case self::POLICY[3]:
                $grand->policy = 3;
                break;
            case self::POLICY[4]:
                $grand->policy = 4;
                break;
            default:
                break;
        }
        return compact('grand', 'user');
    }
    
    /*
     * 更新数据
     */
    public function update(GrandRequest $request, $id)
    {
        //判断发放数量是否大于库存数量
        if (!$this->grandLastStore($request)) {
            return response()->json(['code' => self::CODE['limit']]);
        }
        try {
            DB::beginTransaction();
            //保存发券
            $grand = CouponGrand::findOrFail($id);
            $coupon = $grand->coupon;
            if ($coupon->id != $request->coupon_id) {
                $coupon->update([
                    'flag' => 0,
                    'status' => 0
                ]);
                $coupon1 = Coupon::findOrFail($request->coupon_id);
                $coupon1->update([
                    'flag' => Coupon::FLAG['coupon'],
                    'status' => Coupon::STATUS['used']
                ]);
            }
            $grand->update(array_merge($request->all('name', 'coupon_id', 'grand_type', 'scence', 'range_type'), [
                'amount' => $request->amount ?? null,
                'count' => $request->count ?? null,
                'range_msg' => $request->range_msg ?? null,
                'admin_id' => auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id,
                'grand_time' => $request->grand_time ?? Carbon::now(),
                'chanel_type' => $request->chanel_type ?? 0,
                'period_type' => $request->period_type ?? 0,
                'period_start' => $request->period_start ?? null,
                'period_end' => $request->period_end ?? null,
                'period_day' => $request->period_day ?? 0,
                'unit_time' => $request->unit_time ?? 0
            ]));
            if (!$this->saveUserId($request, $id)) {
                DB::rollback();
                Log::info('coupon_grand_error', ['redis_error']);
                return response()->json(['code' => self::CODE['error']]);
            }
            //立即发券
            if ($request->grand_type == CouponGrand::GRANDTYPE['once']) {
                Artisan::call('set:coupon', ['grand_id' =>$id]);
            } else {
                if (Carbon::parse($grand->grand_time)->timestamp <= Carbon::now()->timestamp) {
                    Artisan::call('set:coupon', ['grand_id' => $id]);
                }
            }
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('coupon_grand_error', [$e]);
            return response()->json(['code' => self::CODE['error']]);
        }
    }

    /*
     * id去重和校验不存在用户
     * user_ids用户的id字符串，格式1;2;3;4
     * grand_id发券id
     */
    public function checkUserId($user_ids, $grand_id)
    {
        $users = explode(';', $user_ids);
        $users = array_unique($users);  //去除重复元素
        $newUserArr = [];  //存储用户id
        foreach ($users as $id) {
            if (User::where('id', $id)->first()) {
                $newUserArr[] = $id;
            }
        }
        IQuery::redisSet(self::REDIS_PATH . $grand_id, $newUserArr);
    }
    
    /*
     * 获取优惠券模板
     */
    public function getTemplate(Request $request) {
        $temp_type = $request->temp_type;
        $policy = self::POLICY[$temp_type];
        $coupons = Coupon::where('flag', 0)->where('status', Coupon::STATUS['start'])->where('policy', $policy)->select('id', 'name', 'count as store', 'no')->get();
        $couponArr = [];
        foreach($coupons as $coupon) {
            $couponArr[] = [
                'id' => $coupon->id,
                'store' => $coupon->store,
                'name' => $coupon->name . ' '. $coupon->no
            ];
        }
        return $couponArr;
    }

    /*
     * redis数据从临时表转移
     * key临时redis,grand发券id
     */
    public function changeRedisPath($key, $grand_id)
    {
        $userArr = IQuery::redisGet($key);
        if (!$userArr) {
            return false;
        }
        IQuery::redisSet(self::REDIS_PATH . $grand_id, $userArr);
        return true;
    }

    /*
     * 用redis存储excel表导入的用户
     * return 返回存储时间戳的key值
     */
    public function excelStore(Request $request, Excel $excel)
    {
        $filePath = IQuery::getExcel($request);
        if (!$filePath) {
            @unlink($filePath);
            return response()->json(['msg' => '上传文件类型错误，请上传xls或xlsx类型文件', 'code' => 2001]);
        }
        try {
            $reader = $excel->load($filePath);
        } catch (\Exception $e) {
            @unlink($filePath);
            return response()->json(['msg' => '请完整填写数据信息', 'code' => 2003]);
        }
        $res = $reader->getSheet(0)->toArray();
        @unlink($filePath);
        if ($res[0][0] !== '用户ID' || $res[0] == 'null' || $res[0] == null) {
            return response()->json(['msg' => '导入excel模板错误,请参照excel模板导入', 'code' => 2002]);
        }
        //excel表数据格式是矩阵格式$res[0][0]为excel表第一个单元格的元素，需二维数组遍历索引
        $user_arr = [];  //存储用户id数组，加载在临时redis里
        for ($i = 1; $i < count($res); $i++) {
            $data = $res[$i];
            if ($data[0]) {
                $user_id = $this->queryUser(trim($data[0]));
                if ($user_id && !in_array($user_id, $user_arr)) {  //验证重复id
                    $user_arr[] = $user_id;
                }
            }
            for($j = 1; $j < count($data); $j++) {
                if ($data[$j]) {
                    return response()->json(['msg' => '导入excel模板错误,请参照excel模板导入', 'code' => 2002]);
                }
            }
        }
        $excelArr = count($user_arr);
        if ($excelArr > 0) {
            $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
            $flag = self::REDIS_TIME . time() . $admin; //时间戳+发券者id
            IQuery::redisSet($flag, $user_arr, 3600);  //存储临时redis
        } else {
            $excelArr = 0;
            $flag = 0;
        }
        return response()->json(['code' => 0, 'excelArr' => $excelArr, 'flag' => $flag]);
    }

    /*
     * 查询用户电话
     * $id用户id,$phone用户手机
     */
    public function queryUser($id)
    {
        $user = User::find($id);
        if ($user) {
            return $user->id;
        }
        return false;
    }

    /*
     * 优惠券上部详细信息
     */
    public function topShow(Request $request, $id)
    {
        $grand = CouponGrand::with('coupon', 'library', 'grandNum', 'useNum', 'unuseNum', 'orderCoupon')->findOrFail($id);
        return $this->response->item($grand, new CouponGrandItemTransformer);
    }

    /*
     * 优惠券明细
     * $id优惠券id
     * keyword关键字(用户昵称、电话、订单号)
     */
    public function show(Request $request, $id)
    {
        $page = $request->page_size != '' ? $request->page_size : config('app.page');
        $librarys = CouponLibrary::where('coupon_grands.id', '=', $id)
            ->leftjoin('coupon_grands', 'coupon_librarys.coupon_id', '=', 'coupon_grands.coupon_id')
            ->leftjoin('users', 'coupon_librarys.user_id', '=', 'users.id')
            ->leftjoin('orders', 'coupon_librarys.order_id', '=', 'orders.id')
            ->select('coupon_librarys.id', 'coupon_librarys.code_id', 'coupon_librarys.status', 'users.name', 'users.phone', 'users.id as user_id',
                     'coupon_librarys.created_at', 'coupon_librarys.used_at', 'orders.no', 'coupon_librarys.coupon_id', 'coupon_librarys.code')
            ->when($request->keyword, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('users.name', 'like', '%' . $value . '%')
                        ->orWhere('users.phone', 'like', '%' . $value . '%')
                        ->orWhere('orders.no', 'like', '%' . $value . '%')
                        ->orWhere('coupon_librarys.code_id', 'like', '%' . $value . '%');
                });
            });
            if ($request->status != '') {
                $librarys = $librarys->where('coupon_librarys.status', $request->status);
            }
        return $this->response->collection($librarys->orderBy('coupon_librarys.code_id')->paginate($page));
    }

    /*
     * 优惠券数量统计
     */
    public function couponNumber(Request $request)
    {
        $sql = "select count(1) as total, ifnull(sum(case when cl.`status` != 0 and cl.created_at is not null then 1 else 0 end), 0) as pick_total, 
                ifnull(sum(case when cl.`status` = 2 then 1 else 0 end), 0) as use_total 
                from coupon_librarys cl left join coupons c on cl.coupon_id = c.id where cl.deleted_at is null and c.flag = 1";
        $res = DB::select($sql);
        foreach($res as $r) {
            $total = $r->total;
            $pick_total = $r->pick_total;
            $use_total = $r->use_total;
        }
        return compact('total', 'pick_total', 'use_total');
    }

    /*
     * 获取指定全部用户
     */
    public function allUser()
    {
        $user_num = User::count();
        return compact('user_num');
    }

    /*
     * 检测指定用户是否合法
     */
    public function checkUser(Request $request)
    {
        $arr_id = explode(';', $request->userVal);
        $arr_id = array_unique($arr_id);  //去除重复用户id
        $exist = '';
        $no_exist = '';
        foreach ($arr_id as $id) {
            if (!preg_match('/^([1-9]\d{0,9})$/', $id)) {
                $no_exist .= $id . ',';
            } else {
                if (User::where('id', $id)->first()) {
                    $exist .= $id . ';';
                } else {
                    $no_exist .= $id . ',';
                }
            }
        }
        $exist = substr($exist, 0, strlen($exist) - 1);
        $no_exist = substr($no_exist, 0, strlen($no_exist) - 1);
        return compact('exist', 'no_exist');
    }

    /*
     * 更改发券状态
     */
    public function changeState($id)
    {
        $grand = CouponGrand::findOrFail($id);
        switch ($grand->status) {
            case CouponGrand::GRANDSTATUS['ungrand']:
            case CouponGrand::GRANDSTATUS['granding']:
                $grand->update(['status' => CouponGrand::GRANDSTATUS['pause']]);
                break;
            case CouponGrand::GRANDSTATUS['pause']:
                if ($grand->grand_type == CouponGrand::GRANDTYPE['spec']) {
                    if ($grand->grand_time >= Carbon::now()) {
                        $grand->update(['status' => CouponGrand::GRANDSTATUS['ungrand']]);
                    } else {
                        $grand->update(['status' => CouponGrand::GRANDSTATUS['finish']]);
                    }
                } else {
                    $grand->update(['status' => CouponGrand::GRANDSTATUS['granding']]);
                }
                break;
            default:
                break;
        }
    }

    /*
     * 前端访问生成校验
     * 返回校验sign
     */
    public function signCheck()
    {
        $sign = md5(IQuery::createCode(16));
        IQuery::redisSet(SELF::REDIS_PATH . 'sign', $sign, 20);  //将校验存进redis,响应校验20秒
        return response()->json($sign);
    }

    /*
     * 下载excel模板
     */
    public function loadExcel(Excel $excel, $sign)
    {
        if (IQuery::redisGet(SELF::REDIS_PATH . 'sign') != $sign) {
            abort(404);
        }
        $datas = [['id' => 'ID1'], ['id' => 'ID2']];
        foreach ($datas as $value) {
            $export[] = array(
                '用户ID' => $value['id']
            );
        }
        IQuery::loadExcel($excel, '模板excel', $export);
    }

    /*
     * 线下优惠券导出生成excel
     */
    public function outCode(Excel $excel, $id, $sign)
    {
        if (IQuery::redisGet(SELF::REDIS_PATH . 'sign') != $sign) {
            abort(404);
        }
        $grand = CouponGrand::findOrFail($id);
        if ($grand->scence == CouponGrand::SCENCE['change']) {  //导出线下券
            $res = CouponLibrary::whereHas('grand', function($query) use($id) {
                $query->where('id', $id);
            })->select('id', 'code_id', 'code', 'period_start', 'period_end', 'status')->get();
            if ($res->isEmpty()) {
                abort(404);
            }
            $coupon = $grand->coupon;
            if ($coupon->period_type) {
                $period_time = '自领取日起'.$coupon->period_day.$this->unitTime($coupon->unit_time).'内有效';
            } else {
                $period_time = $coupon->period_start->format('Y-m-d') . '至' . $coupon->period_end->format('Y-m-d');
            }
            foreach ($res as $r) {
                $export[] = array(
                    '优惠券编码' => $r->code_id,
                    '兑换码' => $r->code,
                    '券有效期' => $period_time,
                    '兑换码有效期' => Carbon::parse($r->period_start)->startOfDay() .'至'. Carbon::parse($r->period_end)->endOfDay(),
                    '兑换状态' => $this->exChangeStatus($r->status)
                );
            }
            $this->outRecordName($grand);
            IQuery::loadExcel($excel, '兑换码excel', $export);
        } else if ($grand->scence == CouponGrand::SCENCE['qrcode']) {  //导出二维码
            $this->outRecordName($grand);
            $qrcode = new QrCode;
            $result = $qrcode->codeCreate($id);
            $filename = '喜茶券二维码.jpg';
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $img = file_get_contents($filename);
            echo $result;
        } else {
            abort(404);
        }
    }
    
    /**
     * 兑换状态
     * $status
     */
    public function exChangeStatus($status) {
        if (!$status) {
            return '未兑换';
        } else {
            return '已兑换';
        }
    }
    
    public function unitTime($unit_time) {
        if ($unit_time == 1) {
            $time = '月';
        } else if ($unit_time == 2) {
            $time = '年';
        } else {
            $time = '天';
        }
        return $time;
    }
    
    /**
     * 导出记录写入
     * @param type $grand
     */
    public function outRecordName($grand) {
        CouponCodeRecord::create([
            'grand_id' => $grand->id,
            'outer_name' => $grand->admin->name ?? $grand->mAdmin->name, 
            'outer_time' => Carbon::now()
        ]);
    }
    
    /**
     * 兑换码、二维码导出记录
     */
    public function codeRecord($grand_id) {
        $record = CouponCodeRecord::where('grand_id', $grand_id)->get();
        return compact('record');
    }
}