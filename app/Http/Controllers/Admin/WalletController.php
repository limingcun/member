<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\WalletRepository;
use IQuery;
use Carbon\Carbon;
use App\Services\CashStorage\Request as Srequest;

/*
 * 钱包控制器
 */
class WalletController extends ApiController
{
    const CODE = [
        'error' => 2002
    ];
    
    const FOUNCODE = [
        'funcode' => 'A3.AD001'
    ];
    
    /**
     * 钱包规则列表
     */
    public function index() {
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'type' => 'list'));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = json_decode($result['contentData'], true);
        $list = $contentData['list'];
        $rps = new WalletRepository();
        $res = $rps->index($list);
        return $res;
    }
    
    /**
     * 余额钱包详情查询
     */
    public function query(Request $request) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $id = $request->id;
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'id' => (integer) $id, 'type' => 'detail'));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = json_decode($result['contentData'], true);
        $list = $contentData['list'];
        $rps = new WalletRepository();
        $res = $rps->index($list);
        return $res;
    }
    
    /**
     * 余额钱包新增
     */
    public function add(Request $request) {
        $this->validate($request, [
            'chargeAmount' => 'required|integer',
            'discountType' => 'required|integer',
            'discountAmount' => 'required|integer',
            'limitCount' => 'required|integer',
            'imgUrl' => 'required|string'
        ]);
        $chargeAmount = $request->chargeAmount;
        $discountType = $request->discountType;
        $discountAmount = $request->discountAmount;
        $limitCount = $request->limitCount;
        $imgUrl = $request->imgUrl;
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'type' => 'add', 'chargeAmount' => $chargeAmount, 'discountType' => $discountType,
                               'discountAmount' => $discountAmount, 'limitCount' => $limitCount, 'imgUrl' => $imgUrl));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = json_decode($result['contentData'], true);
        if ($contentData['responseCode'] == 'SUCCESS') {
            return success_return();
        }
        return response()->json(['code' => self::CODE['error'], 'msg' => '余额钱包新增失败']);
    }
    
    /**
     * 余额钱包详删除
     */
    public function delete(Request $request) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $id = $request->id;
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'id' => (integer) $id, 'type' => 'delete'));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = json_decode($result['contentData'], true);
        if ($contentData['responseCode'] == 'SUCCESS') {
            return success_return();
        }
        return response()->json(['code' => self::CODE['error'], 'msg' => '余额钱包删除失败']);
    }
    
    /**
     * 设置赠送次数
     */
    public function setGiftTime(Request $request) {
        $this->validate($request, [
            'count' => 'required'
        ]);
        $count = $request->count;
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'type' => 'setAllDiscountLimitCount', 'allDiscountLimitCount' => (int) $count));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = json_decode($result['contentData'], true);
        if ($contentData['responseCode'] == 'SUCCESS') {
            return success_return();
        }
        return response()->json(['code' => self::CODE['error'], 'msg' => '设置赠送次数成功']);
    }
    
    /**
     * 重置密码
     */
    public function resetPassword(Request $request) {
        $this->validate($request, [
            'user_id' => 'required'
        ]);
        //$password = '199508';
        $user_id = $request->user_id;
        $random = '536578';
        $password = '16582b2abad6e8651df41908d492a27db1998af2afde6a9cca1c489475c5d5b66828113dde0375aec61a78273a618f68d41ca5daf76d514080d34d57aa5bb4'
                  . '92e2ba883b363bc0383b4f937c605ee5471e2a35bb89ca81c8aeac94b1656d93b4548bf998fbb5e1142815ad7cb3edcdcd993a79f716780434fbdd0b65b3c1'
                  . '29cdcf7b8a9179ef6f0cbac34b5298a9e3b0872e87ead3dfd84b2724867a22dfa9a30f0b9414eb52d039d73f3345f818eb3e9210f3c393090312bf9b458b5c'
                  . '885b0f34366d11e0b4c9c18eac452dfa7d6b2425b1f64f50f82397cce2d13f01213247a3de175aad4b0c2074d44e5fddf06da08afedbaa4f937e71a65e2528a488cad5';
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'userId' => (string) $user_id, 'random' => $random, 'password' => $password));
        $data['funcode'] = 'A1.AC006';
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = $result['contentData'] ?? '';
        if ($contentData == '') {
            return response()->json(['code' => self::CODE['error'], 'msg' => '服务器异常']);
        }
        $contentData = json_decode($contentData, true);
        if ($contentData['code'] == '0000') {
            return success_return();
        }
        return response()->json(['code' => self::CODE['error'], 'msg' => $contentData['errorMsg']]);
    }
}
