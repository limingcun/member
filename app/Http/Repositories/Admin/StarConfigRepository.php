<?php
namespace App\Http\Repositories\Admin;

use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\StarLevel;
use App\Models\User;
use IQuery;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Excel;

class StarConfigRepository extends BaseRepository
{
    protected $table;

    /**
     * 给指定用户发放会员卡
     */
    public function sendCard($user_id, $card_type)
    {
        $member = Member::where('user_id', $user_id)->first();
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time));
        // 续费则为时间累加
        $period_start = Carbon::now();
        if (strtotime($expire_time) >= strtotime(Carbon::today())) {
            $period_start = Carbon::createFromTimestamp(strtotime($expire_time))->addDay();
            $period_end = MemberCardRecord::getPeriodEnd($card_type, clone($period_start))->endOfDay();
        } else {
            // 开始时间以付款时间为准
            $period_end = MemberCardRecord::getPeriodEnd($card_type, Carbon::today())->endOfDay();
        }
        // 发首充
        $is_first = MemberCardRecord::where('user_id', $member->user_id)->where('status', MemberCardRecord::STATUS['is_pay'])
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['experience'])
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['vka'])->select('id')->exists();
        if (!$is_first && ($card_type != MemberCardRecord::CARD_TYPE['experience'])) {
            createCoupon('buy_fee_first_1-1', $user_id, 1);
            createCoupon('cash_star_first', $user_id, 2);
        }
        $star_time = $member->star_time ?? Carbon::now();
        $member_type = $member->member_type > 0 ? $member->member_type : 1;
        $expire_time = $period_end;
//        $member_no = $member->member_no ?? IQuery::memberNoCreate($member->id, 6);
        $star_level_id = StarLevel::where('exp_min', '<=', $member->star_exp)
            ->where('exp_max', '>=', $member->star_exp)
            ->value('id');
        DB::table('members')->where('user_id', $member->user_id)
            ->update([
                'star_level_id' => $star_level_id,
//                'member_no' => $member_no,
                'star_time' => $star_time
            ]);
        $level_change = [
            'star' => [$member->star_level_id, $star_level_id],
            'go' => [$member->level_id, $member->level_id]
        ];
        // 购卡福利
        createCardCoupon($card_type, $user_id);
        // 生日券  生日在本月 且不存在生日赠饮券  就给用户发一张生日赠饮券 可使用日期为会员生日当天
        sendBirthday($user_id);
        // 星球会员日赠饮券
        sendPrimeDayCoupon($user_id);
        // 星球会员纪念日券
        sendAnniversaryCoupon($user_id);
        // 发放每月福利
        $user = User::where('id', $user_id)->select('id')->first();
        createMonthGift($card_type, $user);
        DB::table('members')->where('user_id', $member->user_id)
            ->update(['expire_time' => $expire_time, 'member_type' => $member_type]);
        $data = [
            'user_id' => $user_id,
            'period_start' => $period_start->toDateTimeString(),
            'period_end' => $period_end->toDateTimeString(),
            'card_type' => $card_type,
            'level_change' => $level_change
        ];
        return $data;
    }

    /**
     * 处理上传的excel表
     */
    public function excelHandle($res)
    {
        $unique_arr = array_unique($res);
        // 重复出现的数据
        $wrong_phones = array_diff_assoc($res, $unique_arr);
        // 把重复手机号删除后的数据
        $phones = array_diff($res, $wrong_phones);
        $wrong_phones = array_diff($res, $phones);
        // 错误数据记录 导出excel用
        $excel_phone = [];
        foreach ($wrong_phones as $phone) {
            $excel_phone[] = [$phone, '重复手机号'];
        }
        $user_arr = [];
        foreach ($phones as $phone) {
            $user = $this->queryUser(trim($phone));
            if (count($user) > 1) {
                $excel_phone[] = [$phone, '手机号对应多个user_id'];
            } elseif (count($user) == 1){
                $user_arr[] = $user[0]->id;
            } else {
                $excel_phone[] = [$phone, '手机号未绑定喜茶go'];
            }
        }
        $flag = 0;
        $wrong_flag = 0;
        $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
        if (count($user_arr) > 0) {
            // 导入数据中正确的ids 查询时使用
            $flag = 'star_cfg_user_arr' . time() . '_' . $admin;
            IQuery::redisSet($flag, $user_arr, 3600);
        }
        if (count($excel_phone) > 0) {
            // 错误数据信息，存储在redis中，导出时根据标识取到信息
            $wrong_flag = 'star_cfg_wrong_arr' . time() . '_' . $admin;
            IQuery::redisSet($wrong_flag, $excel_phone, 3600);
        }
        $data = [
            'flag' => $flag,
            'count' => count($user_arr),
            'wrong_flag' => $wrong_flag,
            'wrong_count' => count($excel_phone)
        ];
        return $data;
    }


    /**
     * 生成错误数据excel
     */
    public function createExcel($arrays)
    {
        $cellData = [
            ['手机号','失败原因'],
        ];
        foreach ($arrays as $array) {
            $cellData[] = $array;
        }
        app(Excel::class)->create('error_data',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xlsx');
    }


    /**
     * 根据手机号查找到用户
     */
    public function queryUser($key)
    {
        $users = User::when($key, function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->Where('phone', $value);
            });
        })->select(['id'])->get();
        return $users;
    }

    /**
     * 新增赠送记录
     */
    public function createRecord($data)
    {
        // 新增购卡记录
        $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
        $status = MemberCardRecord::create([
            'user_id' => $data['user_id'],
            'card_no' => 0,       // 付费会员卡不存在卡号 默认为0
            'card_type' => $data['card_type'],
            'price' => 0,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'status' => 1,
            'paid_type' => 3,   // 该字段用于区分是否是后台赠送的
            'paid_at' => Carbon::now(),
            'level_change' => json_encode($data['level_change']),    // 等级变化
            'admin_id' => $admin,   // 操作人
        ]);
        return $status;
    }
}
