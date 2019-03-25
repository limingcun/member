<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\StarConfigRepository;
use App\Models\Level;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\User;
use App\Transformers\Admin\StarConfigRecordsTransformer;
use App\Transformers\Admin\StarConfigTransformer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use IQuery;
use DB;

class StarConfigController extends ApiController
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'key' => 'required|integer'
        ]);
        $members = $this->queryUser($request['key']);
        return $this->response->collection($members, new StarConfigTransformer());
    }

    /**
     * 批量修改 上传文件
     */
    public function upExcel(Request $request, Excel $excel)
    {
        if (!$request->file(['excel_file'])) {
            abort(400, '请上传excel');
        }
        $filePath = IQuery::getExcel($request);
        if (!$filePath) {
            @unlink($filePath);
            abort(400, '上传文件类型错误，请上传xls或xlsx类型文件');
        }
        try {
            $reader = $excel->load($filePath);
        } catch (\Exception $e) {
            @unlink($filePath);
            abort(400, '请完整填写数据信息');
        }
        $res = $reader->getSheet(0)->toArray();
        @unlink($filePath);
        if ($res[0][0] !== 'phone' || $res[0] == 'null' || $res[0] == null) {
            abort(400, '导入excel模板错误,请参照excel模板导入');
        }
        $res = array_column($res, 0);   // 二维数组转一维
        $res = array_splice($res, 1);   // 删掉第一行标题 phone
        $res = array_diff($res, [null, '', ' ']);   // 清除excel中的空白行
        if (!count($res)) {
            return response()->json(['code' => 2001, 'msg' => '上传文件中手机号为空']);
        } elseif (count($res) > 100) {
            return response()->json(['code' => 2002, 'msg' => '单次只能批量处理100条数据']);
        }
        try {
            $repository = new StarConfigRepository();
            $data = $repository->excelHandle($res);
        } catch (\Exception $e) {
            \Log::error('STAR_CONFIG_UPDATE_ERROR', [$e]);
            abort(500, '系统处理失败，请重新上传');
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    /**
     * 确认提交 返回member列表
     */
    public function queryMemberList(Request $request)
    {
        $flag = $request['flag'] ?? false;
        if ($flag) {
            $ids_arr = IQuery::redisGet($flag);
            if ($ids_arr) {
                $members = [];
                foreach ($ids_arr as $id) {
                    $members[] = Member::where('user_id', $id)
                        ->select(['id', 'user_id', 'level_id', 'star_level_id', 'expire_time'])->first();
                }
                return $this->response->collection($members, new StarConfigTransformer());
            }
        }
        return $this->response->json(['data' => []]);
    }

    /**
     * 导出错误数据excel
     */
    public function exportWrongExcel(Request $request)
    {
        $flag = $request['flag'] ?? false;
        if ($flag) {
            $wrong_arr = IQuery::redisGet($flag);
            if (!count($wrong_arr)) {
                abort(400, '没有数据哦');
            }
            try {
                $repository = new StarConfigRepository();
                $repository->createExcel($wrong_arr);
            } catch (\Exception $e) {
                \Log::error('STAR_CONFIG_EXPORT_WRONG_EXCEL_ERROR', [$e]);
                abort(500, '系统处理失败，请稍后再试');
            }
        } else {
            abort(400, '请传入合法的标识');
        }
    }


    /**
     * 更改用户信息
     */
    public function starConfig(Request $request)
    {
        $ids = $request['ids'] ?? null;
        $count = 0;
        if ($ids) {
            $card_type = $request['card_type'];
            if (!in_array($card_type, [3, 4, 5])) {
                return $this->response->json(['code' => 2003, 'msg' => '会员卡类型不存在']);
            }
            $ids = explode(',', $ids);
//            $star_level = $request['star_level_id'];
            if (count($ids) > 100) {
                return $this->response->json(['code' => 2004, 'msg' => '单次最多只能操作100条数据 ']);
            }
            $repository = new StarConfigRepository();
            foreach ($ids as $id) {
                DB::beginTransaction();
                try {
                    $data = $repository->sendCard($id, $card_type);
                    $repository->createRecord($data);
                    $count++;
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('STAR_CONFIG_ERROR', [$e]);
                }
            }
            return $this->response->json(['code' => 0, 'count' => $count]);
        }
        return $this->response->json(['code' => 2005, 'msg' => '请选择用户']);
    }


    /**
     * 根据手机号或id查找到用户
     */
    public function queryUser($key)
    {
        $user = User::where('id', $key)->orWhere('phone', $key)->select(['id'])->get();
        $ids = $user->pluck('id');
        $members = Member::whereIn('user_id', $ids)
            ->select(['id', 'user_id', 'level_id', 'star_level_id', 'expire_time', 'usable_score'])
            ->orderBy('star_level_id', 'DESC')
            ->orderBy('level_id', 'DESC')
            ->get();
        return $members;
    }

    /**
     * 历史调配总人数
     */
    public function starConfigCount()
    {
        $amount = MemberCardRecord::where('card_no', 0)
            ->distinct()->select('user_id')
            ->where('paid_type', 3)
            ->where('price', 0)
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->count('user_id');
        return $this->response->json(['amount' => $amount]);
    }

    /**
     * 获取历史调配记录
     */
    public function starConfigRecords(Request $request)
    {
        $keyword = $request['keyword'] ?? null;
        $user_ids = null;
        if ($keyword) {
            $members = Member::when($request['keyword'], function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('user_id', $value)->orWhere('phone', $value);
                });
            })->get(['user_id']);
            $user_ids = $members->pluck('user_id');
        }
        $records = MemberCardRecord::when($user_ids, function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereIn('user_id', $value);
            });
        })->where('card_no', 0)->where('paid_type', 3)
            ->where('price', 0)->where('status', MemberCardRecord::STATUS['is_pay'])
            ->orderBy('id', 'DESC')->paginate($request['page_size'] ?? 10);
        return $this->response->collection($records, new StarConfigRecordsTransformer());
    }


    /**
     * 给指定的用户调整等级
     */
    public function upStarLevel($user, $level_id)
    {
        $star_exp = Level::where('id', $level_id)->value('exp_min');
        // todo 下期需求
    }

    /**
     * 下载模板
     */
    public function template()
    {
        $cellData = [
            ['phone'],
            ['15666666666'],
        ];
        try {
            app(Excel::class)->create('template', function ($excel) use ($cellData) {
                $excel->sheet('score', function ($sheet) use ($cellData) {
                    $sheet->rows($cellData);
                });
            })->export('xlsx');
        } catch (\Exception $e) {
            \Log::error('STAR_CONFIG_DOWNLOAD_TEMPLATE', $e);
            abort(500, '系统处理失败，请稍后再试');
        }
    }
}
