<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/29
 * Time: 上午10:13
 * desc: 给员工设置福利
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use DB;
use IQuery;
use Maatwebsite\Excel\Excel;

class GiftEmployeeWelfare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gift:welfare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '给员工发福利';
    
    protected $redis_path = 'laravel:employee_welfare:';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Excel $excel)
    {
        if (Carbon::today()->format('d') != 1) {
            return;
        }
        $redis_department = $this->redis_path.'department';
        $redis_shop = $this->redis_path.'shop';
        if (IQuery::redisExists($redis_department) || IQuery::redisExists($redis_shop)) {
            return;
        }
        $fileName = 'public/excel/istore.xlsx';
        if (!is_file($fileName)) {
            return;
        }
        DB::beginTransaction();
        try {
            $reader = $excel->load($fileName);
            $department = $reader->getSheet(0)->toArray();
            IQuery::redisSet($redis_department, $department);
            $shop = $reader->getSheet(1)->toArray();
            IQuery::redisSet($redis_shop, $shop);
            unlink($fileName);
            DB::commit();
            Log::info('laravel:employee_welfare_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('laravel:employee_welfare_error', [$exception]);
        } 
    }
}
