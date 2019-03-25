<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/08
 * Time: 下午16:39
 * desc: 定时给用户发券
 */

namespace App\Console\Commands;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use DB;
use IQuery;

class AddressUpdate extends Command
{
    const url = 'http://restapi.amap.com/v3/geocode/regeo?output=json&location=';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'address:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时更新用户详细地址';

    protected $path = 'laravel:address:';
    
    private $key;
    
    private $limit;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->key = env('GAODE_APP_KEY');
        $this->limit = 300;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = IQuery::redisGet($this->path.'_complete');
        if ($redis) {
            $offset = 0;
        } else {
            $offset = $redis;
        }
        $res = DB::table('addresses')->whereNull('deleted_at')->whereNull('complete_address')->offset($offset)->limit($this->limit)->get();
        if ($res->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach($res as $r) {
                $address = $r->longitude.','.$r->latitude;
                $url = self::url.$address. '&key=' .$this->key;
                if($result=file_get_contents($url)) {
                    $result = json_decode($result,true);
                    if(!empty($result['status'])&&$result['status']==1){
                        $complete_address = $result['regeocode']['formatted_address'];
                        if (empty($complete_address)) {
                            $complete_address = '未知详细地址';
                        }
                        \DB::update('update addresses set complete_address = "'.$complete_address .'" where id = '.$r->id);
                    }
                }
            }
            IQuery::redisSet($this->path.'_complete', $offset + $this->limit, 300);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info('update_address_error', [$exception]);
        }
    }
}
