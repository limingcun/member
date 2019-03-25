<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Listeners\OrderStatusUpdate;
use App\Models\Order;
use Log;

class OrderStatusUpdateEventTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testOrderPaid()
    {
        $order = Order::where('user_id', 7)->orderBy('created_at', 'desc')->first();
        $osu = new OrderStatusUpdate();
        if ($order->status == 'BUYER_PAY') {
            $msg = $osu->handle($order);
            if ($msg) {
                $this->assertArraySubset([
                    "errcode" => 0,
                    "errmsg" =>  "ok"
                ], $msg);
            }
            $this->assertNull($msg);
        }
    }
}
