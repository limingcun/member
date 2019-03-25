<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/6/11
 * Time: 下午14:10
 * desc: 定时删除Log文件
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class DeleteLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'del:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '保留一周之前的日志文件';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $log = 'logs/';
        $path = str_replace('\\', '/', storage_path().'/'.$log);
        $handler = opendir($path);
        while(($filename = readdir($handler)) !== false ) {
            if($filename != '.' && $filename != '..' && $filename != '.gitignore') {
                if (Carbon::now()->subWeek()->format('Y-m-d') >= date('Y-m-d', filectime($path.$filename))) {
                    @unlink($path.$filename);
                    Log::info('del:log', [$path.$filename]);
                }
            }
        }
        closedir($handler);
    }
}
