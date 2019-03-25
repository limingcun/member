<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class WalletRepository extends BaseRepository
{
    public function __construct()
    {
        
    }

    public function index($list)
    {
        $page = (request('page', 1) - 1) * config('app.page');
        $larr = [];
        foreach($list as $li) {
            $larr[] = $li;
        }
        $res = $this->pageSize($page, $larr, 10);
        return $res;
    }
}
