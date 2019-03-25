<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use function compact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function success_return;
use DB;

class ShopController extends Controller
{
    /**
     * 获取全部省份
     */
    public function province() {
        $province = Shop::select('province')->groupBy('province')->get();
        return response()->json($province);
    }
    
    /**
     * 获取省份级联城市
     * @return type
     */
    public function city(Request $request) {
        $province = $request->province;
        $city = Shop::select('city')->where('province', $province)->groupBy('city')->get();
        return response()->json($city);
    }
    
    /**
     * 
     * @return type
     */
    public function shopList()
    {
        $shops = Shop::select(Shop::$base)
            ->when(request('province'), function ($query, $value) {
                $query->where('province', $value);
            })->when(request('city'), function ($query, $value) {
                $query->where('city', $value);
            })->when(request('keyword'), function ($query, $value) {
                $query->whereLike('name', "%$value%");
            })->when(request('ids'), function ($query, $value) {
                $query->whereNotIn('id', $value);
            })->get();
        $excludes = [];
        if ($ids = request('ids')) {
            $excludes = Shop::select(Shop::$base)
                ->whereIn('id', request('ids'))
                ->get();
        }
        return success_return(compact('shops', 'excludes'));
    }

    public function category()
    {
        $categories = Category::select(['id', 'name'])
            ->orderBy('sort')
            ->get();
        return success_return($categories);
    }

    public function product()
    {
        $products = Product::select(['id', 'name'])
            ->when(request('category_id'), function ($query, $value) {
                $query->where('category_id', $value);
            })
            ->when(request('keyword'), function ($query, $value) {
                $query->where('name', 'like', "%$value%");
            })->when(request('ids'), function ($query, $value) {
                $query->whereNotIn('id', $value);
            })
            ->get();
        $excludes = [];
        if ($ids = request('ids')) {
            $excludes = Product::select(['id', 'name'])
                ->whereIn('id', request('ids'))
                ->get();
        }
        return success_return(compact('products', 'excludes'));
    }
    
    /*
     * 返回所有加料数据信息
     */
    public function material() {
        $material = DB::table('materials')->where('is_actived', 1)->whereNull('deleted_at')->select('id', 'name')->get();
        return response()->json($material);
    }
}
