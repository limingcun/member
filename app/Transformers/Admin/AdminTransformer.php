<?php

namespace App\Transformers\Admin;

use App\Models\Admin;
use League\Fractal\TransformerAbstract;

class AdminTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'permissions',
    ];

    protected $defaultIncludes = [
        'permissions',
    ];

    /**
     * Transform a admin.
     *
     * @param  Admin $admin
     *
     * @return array
     */
    public function transform(Admin $admin)
    {
        return [
            'id' => $admin->id,
            'wechat_userid' => $admin->wechat_userid,
            'name' => $admin->name,
            'english_name' => $admin->english_name,
            'email' => $admin->email,
            'mobile' => $admin->mobile,
//            'avatar' => url($admin->avatar ? $admin->avatar->path : 'images/istore.jpg'),
            'is_super_admin' => $admin->isSuperAdmin(),
            'can_scan' => $admin->can_scan,
            'created_at' => (string) $admin->created_at,
            'updated_at' => (string) $admin->updated_at,
        ];
    }

    public function includePermissions(Admin $item)
    {
        if ($permissions = $item->getAllPermissions()) {
            return $this->collection($permissions, new PermissionTransformer);
        }
    }
}
