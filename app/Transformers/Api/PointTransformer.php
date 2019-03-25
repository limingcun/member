<?php

namespace App\Transformers\Api;

use App\Models\MemberScore;
use Carbon\Carbon;
use function dd;
use League\Fractal\TransformerAbstract;

class PointTransformer extends TransformerAbstract
{
    public function transform(MemberScore $memberScore)
    {
        switch($memberScore->method) {
            case MemberScore::METHOD['cost']:
            case MemberScore::METHOD['refund']:
            case MemberScore::METHOD['star_date']:
                return [
                    'id' => $memberScore->id,
                    'score_change' => $memberScore->score_change,
                    'method' => $memberScore->method,
                    'description' => $memberScore->description,
                    'created_at' => (string) $memberScore->created_at,
                    'no' => $memberScore->source->no ?? '',
                    'payment' => $memberScore->source->payment ?? $memberScore->source->price ?? 0,
                    'shop_name' => $memberScore->source->shop->name ?? '',
                    'source_type' => $memberScore->source_type
                ];
            case MemberScore::METHOD['active']:
            case MemberScore::METHOD['change']:
            case MemberScore::METHOD['mall_refund']:
            case MemberScore::METHOD['vka']:
            case MemberScore::METHOD['star_update']:
            case MemberScore::METHOD['task']:
            case MemberScore::METHOD['game']:
            case MemberScore::METHOD['custom']:
                return [
                    'id' => $memberScore->id,
                    'score_change' => $memberScore->score_change,
                    'method' => $memberScore->method,
                    'description' => $memberScore->description,
                    'created_at' => (string) $memberScore->created_at
                ];
            default:
                return [];
        }
    }
}
