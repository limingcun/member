<?php

namespace App\Transformers\Admin;

use Spatie\Permission\Models\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * Transform a permission.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $permission->label,
            'guard_name' => $permission->guard_name,
            'created_at' => (string) $permission->created_at,
            'updated_at' => (string) $permission->updated_at,
        ];
    }
}
