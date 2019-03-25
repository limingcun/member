<?php

namespace App\Console\Commands;

use Eureka\EurekaClient;
use Illuminate\Console\Command;

class RegisterEureka extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:eureka {cmd?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '加入注册中心';

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
        $command = $this->argument('cmd');
        $ip = trim(shell_exec('ifconfig eth0 | grep "inet " | awk \'{print $2}\' '));
        $appUrl = 'http://' . $ip;
        $client = new EurekaClient([
            'eurekaDefaultUrl' => config('app.eureka_url'),
            'hostName' => $ip,
            'appName' => 'service-member',
            'ip' => '127.0.0.1',
            'port' => ['80', true],
            'homePageUrl' => $appUrl,
            'statusPageUrl' => $appUrl . '/info',
            'healthCheckUrl' => $appUrl . '/health'
        ]);
        if ('stop' == $command) {
            $client->deRegister();
        } else {
            $client->start();
        }
    }
}
