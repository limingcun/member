<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/26
 * Time: 上午10:31
 * desc: 积分控制器
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\Point\RuleRequest;
use App\Transformers\Admin\PointRecordTransformer;
use App\Transformers\Admin\MemberScoreTransformer;
use App\Http\Repositories\Admin\PointRepository;
use App\Models\Member;
use App\Models\MemberScore;
use App\Models\User;
use Illuminate\Http\Request;

class PointController extends ApiController
{
    /**
     * 积分记录
     */
    public function record(Request $request)
    {
        $rps = new PointRepository();
        $result = $rps->index($request);
        return $this->response->collection($result['data'], new PointRecordTransformer(), ['pagination' => $result['pagination']]);
    }

    /**
     * 积分记录总数
     */
    public function record_total()
    {
        $rps = new PointRepository();
        $total = $rps->total();
        return success_return($total);
    }

    /**
     * 积分明细
     */
    public function detail(Request $request)
    {
        $rps = new PointRepository();
        $result = $rps->detail($request);
        return $this->response->collection($result['data'], new MemberScoreTransformer(), ['pagination' => $result['pagination']]);
    }
}