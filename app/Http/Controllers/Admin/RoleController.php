<?php

namespace App\Http\Controllers\Admin;

use App\Models\MPermission;
use App\Models\MRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $role = MRole::select('m_role.*')
            ->orderBy('m_role.id', 'desc')
            ->when(\request('no'), function (Builder $query, $value) {
                $query->where('no', 'like', "%$value%");
            })
            ->when(\request('name'), function (Builder $query, $value) {
                $query->where('name', 'like', "%$value%");
            })
            ->when(\request('permissions'), function (Builder $query, $value) {
                $query->join('m_role_permissions', 'm_role_permissions.role_id', '=', 'm_role.id')
                    ->join('m_permissions', 'm_permissions.id', '=', 'm_role_permissions.permission_id')
                    ->where('m_permissions.name', 'like', "%$value%");
            })
            ->with(['permission'])
            ->distinct('m_role.id')
            ->paginate(100000);
        return success_return($role);

    }

    public function store(Request $request)
    {
        $field = $request->all();
        if (MRole::where('name', $field['name'])->first()) return error_return(-1, '名字不能重复');
        $field['no'] = sprintf("R%03d", MRole::max('id') + 1);
        $role = MRole::create($field);
        return success_return($role);
    }

    public function update(Request $request, $id)
    {
        $role = MRole::findOrFail($id);
        $role->update($request->all());
        return success_return();
    }

    public function show($id)
    {
        $role = MRole::find($id);
        return success_return($role);
    }

    public function destroy($id)
    {
        MRole::destroy($id);
        return success_return();
    }

    public function authPermission($id)
    {
        $role = MRole::findOrFail($id);
        $role->permission()->sync(\request('ids'));
        return success_return();
    }

    public function permission()
    {
        $permission = MPermission::get();
        return success_return($permission);
    }
}

