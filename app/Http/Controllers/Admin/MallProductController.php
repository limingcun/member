<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallSku;
use App\Models\MallSpecification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\MallProduct;
use App\Http\Requests\Admin\Mall\MallProductRequest;
use App\Transformers\Admin\MallProductItemTransformer;
use App\Models\Coupon;
use Carbon\Carbon;
use App\Policies\CouponLibrary\CashCouponPolicy;
use DB;
use Log;

class MallProductController extends ApiController
{
    /*
     * 接受前端传过来的状态数据
     * all全部
     * wait待上架
     * buying售卖中
     * sellout已售罄
     * takedown已下架
     */
    const HOME_REQUEST_STATUS = [
        'all' => 0,
        'wait' => 1,
        'buying' => 2,
        'sellout' => 3,
        'takedown' => 4
    ];

    /*
     * 积分商城商品列表
     * keyword搜索关键词(模糊搜索)
     * page_size页码数量
     * status商品状态(待上架、上架和下架)
     * start上架开始时间
     * end上架结束时间
     * score_min积分最小值
     * score_max积分最大值
     * sort排序数组['字段','asc']
     */
    public function index(Request $request)
    {
        $page = $request->page_size != '' ? $request->page_size : config('app.page');
        $mall_products = MallProduct::where('member_type', $request->member_type)->with('source')->when($request->keyword, function ($query, $value) {
            $query->where(function($query) use($value) {
                $query->where('name', $value)->orWhere('no_code', $value);
            });
        })->when($request->status, function ($query, $value) {
            switch ($value) {
                case self::HOME_REQUEST_STATUS['wait']:
                    $query->where('status', MallProduct::STATUS['wait']);
                    break;
                case self::HOME_REQUEST_STATUS['buying']:
                    $query->where('status', MallProduct::STATUS['takeup'])->where(function($query) {
                        $query->where(function ($query) {
                            $query->where('is_specification', MallProduct::SPCIFICATION['single'])->where('store', '>', 0);
                        })->orWhere(function ($query) {
                            $query->where('is_specification', MallProduct::SPCIFICATION['more'])->whereHas('skus', function ($q) {
                                $q->where('is_show', 1)->havingRaw('sum(store) > 0');
                            });
                        });
                    });
                    break;
                case self::HOME_REQUEST_STATUS['sellout']:
                    $query->where('status', MallProduct::STATUS['takeup'])->where(function($query) {
                        $query->where(function ($query) {
                            $query->where('is_specification', MallProduct::SPCIFICATION['single'])->where('store', 0);
                        })->orWhere(function ($query) {
                            $query->where('is_specification', MallProduct::SPCIFICATION['more'])->whereHas('skus', function ($q) {
                                $q->where('is_show', 1)->havingRaw('sum(store) = 0');
                            });
                        });
                    });
                    break;
                case self::HOME_REQUEST_STATUS['takedown']:
                    $query->where('status', MallProduct::STATUS['takedown']);
                    break;
                default:
                    break;
            }
        })->when($request->start, function ($query, $value) {
            $query->whereDate('shelf_time', '>=', $value);
        })->when($request->end, function ($query, $value) {
            $query->whereDate('shelf_time', '<=', $value);
        })->when($request->score_min, function ($query, $value) {
            $query->where('score', '>=', $value);
        })->when($request->score_max, function ($query, $value) {
            $query->where('score', '<=', $value);
        })->when($request->mall_type, function ($query, $value) {
            $query->where('mall_type', $value);
        });
        if ($request->sort) {
            $sort = json_decode($request->sort, true);
            $mall_products = $mall_products->orderBy($sort[0], $sort[1]);
        } else {
            $mall_products = $mall_products->orderBy('status', 'asc')->orderBy('id', 'desc');
        }
        $mall_products = $mall_products->with([
            'images',
            'skus',
        ]);
        return $this->response->collection($mall_products->paginate($page));
    }

    /*
     * 商品编辑
     * id商品id
     */
    public function edit($id)
    {
        $mall_product = MallProduct::findOrFail($id);
        if ($mall_product->is_specification) {
            $mall_product->load([
                'specification' => function ($query) {
                    $query->orderBy('sort');
                },
                'skus' => function ($query) {
                    $query->orderBy('sort');
                },
            ]);
        }
        if ($mall_product->source_id) {
            $mall_product->load('source');
        }
        return $this->response->item($mall_product, new MallProductItemTransformer());
    }

    /*
     * 积分商城商品新增
     * name商品名称
     * score兑换积分
     * store库存
     * limit_purchase限购数量
     * mall_type商品类型(1表示虚拟商品，2表示实体商品)
     * remark商品说明
     * image_id图片id
     */
    public function store(MallProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $field = [];
            switch ($request->mall_type) {
                case MallProduct::MALLTYPE['invent']:   //保存虚拟商品
                    $field = [
                        'source_id' => $request->coupon_id,
                        'source_type' => Coupon::class,
                        'no_code' => create_no('GN')
                    ];
                    Coupon::where('id', $request->coupon_id)->update(['flag' => Coupon::FLAG['mall'], 'status' => Coupon::STATUS['used']]);
                    break;
                case MallProduct::MALLTYPE['real']:
                    $field = [
                        'source_id' => 0,
                        'source_type' => '',
                        'no_code' => create_no('GN')
                    ];
                    break;
            }
            $mall_product = MallProduct::create(array_merge($request->all(), $field));
            if ($mall_product->is_specification) {
                $this->saveSku($mall_product->id);
            }
            if ($request->image_id) {
                $mall_product->images()->sync($request->image_id);
            }
            DB::commit();
            return success_return();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info('mall_product', [$exception]);
            throw $exception;
        }
    }

    /*
     * 积分商城商品更新
     * name商品名称
     * score兑换积分
     * store库存
     * limit_purchase限购数量
     * mall_type商品类型(1表示虚拟商品，2表示实体商品)
     * remark商品说明
     * period_type过期类型（0表示固定过期，1表示相对过期）
     * period_start过期开始时间
     * period_end过期结束时间
     * period_day过期天数
     * image_id图片id
     */
    public function update(MallProductRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $mall_product = MallProduct::findOrFail($id);
            $mall_type = $mall_product->mall_type;
            $mall_product->update($request->all());
            if ($mall_product->is_specification) {
                $this->saveSku($mall_product->id);
            }
            if ($request->image_id) {
                $mall_product->images()->sync($request->image_id);
            }
            DB::commit();
            return success_return();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info('mall_product', [$exception]);
            return error_return(403);
        }
    }

    /*
     * 商品状态变化(上架，下架)
     * id商品id
     */
    public function statusChange($id)
    {
        $mall_product = MallProduct::findOrFail($id);
        switch ($mall_product->status) {
            case MallProduct::STATUS['wait']:
                $mall_product->status = MallProduct::STATUS['takeup'];
                $mall_product->shelf_time = Carbon::now();
                break;
            case MallProduct::STATUS['takeup']:
                $mall_product->status = MallProduct::STATUS['takedown'];
                break;
            case MallProduct::STATUS['takedown']:
                $mall_product->status = MallProduct::STATUS['takeup'];
                $mall_product->shelf_time = Carbon::now();
                break;
        }
        $mall_product->save();
        return success_return();
    }

    /*
     * 商品库存增加
     * number库存增加数量
     * id商品id
     */
    public function addStore(Request $request, $id)
    {
        $this->validate($request, [
            'number' => 'required|integer|min:1'
        ]);
        $mall_product = MallProduct::with('source')->findOrFail($id);
        $store = $mall_product->store;
        $number = $request->number;
        $mall_product->store = $store + $number;
        if ($mall_product->mall_type == 1) {
            $source_count = $mall_product->source->count;
            $num = ($mall_product->store - $source_count);
            if ($num > 0) {
                return response()->json(['code' => 2003, 'msg' => '商品库存最多还可以添加'.($source_count - $store).'个']);
            }
        }
        $mall_product->save();
        return success_return($mall_product->store);
    }
    
    /**
     * 积分商城预览页
     */
    public function scanMall($id) {
        $mall_product = MallProduct::findOrFail($id);
        if ($mall_product->is_specification) {
            $mall_product->load([
                'specification' => function ($query) {
                    $query->orderBy('sort');
                },
                'skus' => function ($query) {
                    $query->orderBy('specificationIds');
                },
            ]);
        }
        return $this->response->item($mall_product, new \App\Transformers\Api\MallProductItemTransformer());
    }



    /*
     * 商品删除
     * id商品id
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $mall_product = MallProduct::where('status', MallProduct::STATUS['wait'])->findOrFail($id);
            if ($mall_product->source) {
                $mall_product->source->update([
                    'flag' => 0,
                    'status' => 0
                ]);
            }
            $mall_product->delete();
            DB::commit();
            return success_return();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info('mall_product', [$exception]);
        }
    }

    /*
     * 商品批量下架
     * ids数组
     */
    public function takeStock(Request $request)
    {
        $this->validate($request, [
            'ids' => 'array'
        ]);
        $ids = $request->ids;
        MallProduct::whereIn('id', $ids)->update(['status' => MallProduct::STATUS['takedown']]);
        return success_return();
    }

    /**
     * 保存sku
     * @param $mall_product_id
     */
    private function saveSku($mall_product_id)
    {
        $skus = request('skus');
        $specCollect = MallSpecification::where('mall_product_id', $mall_product_id)->get();//现有规格
        $skuCollect = MallSku::where('mall_product_id', $mall_product_id)->get();//现有sku
        $useCollectIds = [];//用到的规格
        $useSpecificationIds = [];//用到的sku
        foreach ($skus as $index => $sku) {
            $specs = new Collection();
            //构造规格
            foreach ($sku['specification'] as $name => $value) {
                $spec = $specCollect->where('name', $name)->where('value', $value)->first();
                if (!$spec) {
                    $spec = MallSpecification::create([
                        'mall_product_id' => $mall_product_id,
                        'name' => $name,
                        'value' => $value,
                        'sort' => $index,
                    ]);
                    $specCollect->add($spec);
                } else {
                    $spec->update(['sort' => $index]);
                }
                $useCollectIds[] = $spec->id;
                $specs->add($spec);
            }
            $specificationIds = implode(',', $specs->pluck('id')->sort()->toArray());
            $useSpecificationIds[] = $specificationIds;
            $mallSku = $skuCollect->where('specificationIds', $specificationIds)->first();
            if ($mallSku) {
                $mallSku->update([
                    'no' => $sku['no'],
                    'store' => $sku['store'],
                    'is_show' => $sku['is_show'],
                    'sort' => $index,
                ]);
            } else {
                $mallSku = MallSku::create([
                    'specificationIds' => $specificationIds,
                    'mall_product_id' => $mall_product_id,
                    'no' => $sku['no'],
                    'store' => $sku['store'],
                    'is_show' => $sku['is_show'],
                    'sort' => $index,
                ]);
                $skuCollect->add($mallSku);
            }
        }
        //清理无用规格
        $specCollectIds = $specCollect->pluck('id')->toArray();
        $delCollectIds = array_diff($specCollectIds, array_intersect($useCollectIds, $specCollectIds));
        if (count($delCollectIds)) {
            MallSpecification::whereIn('id', $delCollectIds)
                ->where('mall_product_id', $mall_product_id)->delete();
        }
        $skuCollectIds = $skuCollect->pluck('specificationIds')->toArray();
        $delSkuIds = array_diff($skuCollectIds, array_intersect($useSpecificationIds, $skuCollectIds));
        if (count($delSkuIds)) {
            MallSku::whereIn('specificationIds', $delSkuIds)
                ->where('mall_product_id', $mall_product_id)->delete();
        }
    }

    /*
     * 商品输入数字排序
     * $id商品列表id
     * $sort排序字段
     */
    public function sort(Request $request, $id)
    {
        $this->validate($request, [
            'sort' => 'integer'
        ]);
        MallProduct::where('id', $id)->update(['sort' => $request->sort]);
        return success_return();
    }
}
