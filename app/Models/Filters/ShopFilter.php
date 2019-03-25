<?php namespace App\Models\Filters;

use App\Services\GaodeMap;
use EloquentFilter\ModelFilter;

class ShopFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function location($value)
    {
        $coordinate = explode(',', $value);
        if (count($coordinate) != 2) {
            abort(500, 'location 参数错误');
        }

        list($longitude, $latitude) = $coordinate;
        $gaodeMap = app(GaodeMap::class);
        $geoinfo = $gaodeMap->geoInfo($longitude, $latitude);
        $cityCode = $geoinfo['addressComponent']['citycode'];

        return $this->where('city_code', $cityCode);
    }

    public function name($value)
    {
        return $this->where(function ($query) use ($value) {
            return $query->where('name', 'like', '%'.$value.'%')
                ->orWhere('address', 'like', '%'.$value.'%');
        });
    }

    public function city($value)
    {
        return $this->where(function ($query) use ($value) {
            $query->where('province', $value)
               ->orWhere('city', $value);
        });
    }

    public function id($value)
    {
        return $this->where(function ($query) use ($value) {
            $query->where('id', $value);
        });
    }
}
