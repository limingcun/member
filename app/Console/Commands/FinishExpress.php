<?php

namespace App\Console\Commands;

use App\Models\MallOrder;
use App\Services\JuheExp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinishExpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delay:express';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改完成的快递单的状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = MallOrder::where('status', MallOrder::STATUS['dispatching'])
            ->with([
                'express'
            ])
            ->where('updated_at', '<', Carbon::today()->subDay(15))->get();
        $JuheExp = new JuheExp();
        foreach ($orders as $order) {
            $express = $order->express;
            $delivery = $JuheExp->query($express->shipper_code, $express->no);
            if (is_array($delivery) && $delivery['status'] == 1) {
                $order->update([
                    'status' => MallOrder::STATUS['finish']
                ]);
                $express->update([
                    'trace' => $delivery['list']
                ]);
            }
        }
    }
}
