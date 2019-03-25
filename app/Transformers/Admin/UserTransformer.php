<?php

namespace App\Transformers\Admin;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * Transform a user.
     *
     * @param  User $user
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
        ];
    }
}
