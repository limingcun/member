<?php

namespace App\Models\Filters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class OrderFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function status($status)
    {
        return $this->where('status', $status);
    }

    public function statuses($statuses)
    {
        if (is_string($statuses)) {
            $statuses = explode(',', $statuses);
        }
        return $this->whereIn('status', $statuses);
    }

    public function keyword($value)
    {
        return $this->where(function ($query) use ($value) {
            $query->where('no', $value)
                ->orWhere('pickup_no', $value)
                ->orWhere('phone', $value)
                ->orWhereHas('user', function ($query) use ($value) {
                    $query->where('name', 'like', '%' . $value . '%');
                });
        });
    }

    public function type($value)
    {
        if ($value == 'EXCEPTIONAL') {
            return $this->whereHas('delivery', function ($query) use ($value) {
                $query->whereDeliveryStatus($value);
            });
        } else {
            return $this->where('status', $value);
        }
    }

    public function pickupNo($value)
    {
        $now = now();
        if ($now->hour > 6) {
            //  凌晨六点以后的
            $start = Carbon::today()->addHours(6);
        } else {
            // 凌晨的从昨天6点开始
            $start = Carbon::yestorday()->addHours(6);
        }
        return $this->where('pickup_no', $value)
            ->where('created_at', '>', $start);
    }

    public function updatedAt($value)
    {
        return $this->where('updated_at', '>=', $value);
    }

    public function today($value)
    {
        $start = Carbon::today();
        $end = Carbon::tomorrow();
        if ($value) {
            return $this->where('created_at', '>', $start)
                ->where('created_at', '<', $end);
        }
    }
}
