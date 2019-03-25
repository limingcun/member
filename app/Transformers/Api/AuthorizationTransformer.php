<?php

namespace App\Transformers\Api;

use App\Models\Authorization;
use League\Fractal\TransformerAbstract;

class AuthorizationTransformer extends TransformerAbstract
{
    public function transform(Authorization $authorization)
    {
        return $authorization->toArray();
    }
}
