<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberScore;
use App\Models\ScoreRule;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Log;
use Illuminate\Console\Command;

class ClearScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clr:score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定期清理积分';

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
        $rule = ScoreRule::orderBy('id')->first();
        if ($rule->expiry_type != 2) return; //积分永不清零
        $cend = str_replace('0', '', Carbon::now()->subMonth($rule->months)->format('m-d'));
        $cstart = str_replace('0', '', Carbon::now()->format('m-d'));;
        if ($rule->expiry_time != $cend || $rule->expiry_time != $cstart) return; //不是今天积分清0
        //今天清0
        $endDate = Carbon::now()->subMonth($rule->months)->format('Y-m-d');  //积分清0结束时间间隔
        $startDate = Carbon::now()->subMonth(2 * $rule->months)->format('Y-m-d'); //积分清0开始时间间隔
        User::select(['id'])->chunk(1000, function ($users) use ($rule, $startDate, $endDate) {
            DB::beginTransaction();
            try {
                foreach ($users as $user) {
                    //上周期结束时的积分状态
                    $endScore = $user->score()->where('created_at', '>=', $startDate)->where('created_at', '<', $endDate)->sum('score_change');
                    //周期外减少积分
                    $cutScore = $user->score()->where('created_at', '>=', $endDate)->whereIn('method', [10, 11])->sum('score_change') ?? 0;
                    $nowScore = $user->member()->usable_score;
                    //有过期积分则清理
                    if ($endScore > 0 && bcsub($endScore, $cutScore) >= 0) {
                        $user->score()->create([
                            'source_id' => $rule->id,
                            'source_type' => ScoreRule::class,
                            'score_change' => bcsub($endScore, $cutScore),
                            'method' => MemberScore::METHOD['expire'],
                        ]);
                        Member::where('user_id', $user->id)->update([
                            'usable_score' => bcsub($nowScore - bcsub($endScore, $cutScore))
                        ]);
                    }
                }
                DB::commit();
                Log::info('clr:score success', $users->pluck('id'));
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::info('clr:score error', [$exception]);
            }
        });
    }
}
