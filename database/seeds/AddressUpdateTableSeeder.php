<?php

use Illuminate\Database\Seeder;
class AddressUpdateTableSeeder extends Seeder
{
    const url = 'http://restapi.amap.com/v3/geocode/regeo?output=json&location=';
    private $key;


    public function __construct() {
        $this->key = env('GAODE_APP_KEY');
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::beginTransaction();
        try {
            $res = \DB::select('select id, latitude, longitude from addresses where deleted_at is null');
            foreach($res as $r) {
                $address = $r->longitude.','.$r->latitude;
                $url = self::url.$address. '&key=' .$this->key;
                 if($result=file_get_contents($url)) {
                    $result = json_decode($result,true);
                    if(!empty($result['status'])&&$result['status']==1){
                        $complete_address = $result['regeocode']['formatted_address'];
                        \DB::update('update addresses set complete_address = "'.$complete_address .'" where id = '.$r->id);
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::info('update_address_error', [$exception]);
        }
    }
}
