<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminedRequest;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MAdmin;
use Illuminate\Database\Eloquent\Builder;

class AdminController extends Controller
{
    public function index()
    {
        $MAdmin = MAdmin::orderBy('id', 'desc')
            ->when(request('no'), function (Builder $query, $value) {
                $query->where('no', 'like', "%$value%");
            })
            ->when(request('mobile'), function (Builder $query, $value) {
                $query->where('mobile', 'like', "%$value%");
            })
            ->when(request('name'), function (Builder $query, $value) {
                $query->where('name', 'like', "%$value%");
            })
            ->when(request('department'), function (Builder $query, $value) {
                $query->where('department', $value);
            })
            ->with([
                'role'=>function($query){
                    $query->where('status',1);
                },
            ]);
        $status = request('status');
        if(!is_null($status)){
            $MAdmin=$MAdmin->where('status',$status);
        }
        $admin = $MAdmin->paginate(10);
        return success_return($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = MAdmin::findOrFail($id);
        $admin->update($request->all());
        return success_return();
    }

    public function store(Request $request)
    {
        $field = $request->all();
        if (MAdmin::where('email', $field['email'])->count()) return ['code' => -1, 'msg' => '邮箱已存在'];
        if (MAdmin::where('mobile', $field['mobile'])->count()) return ['code' => -1, 'msg' => '手机号码已存在'];
        $field['no'] = sprintf("istore%03d", MAdmin::max('id') + 1);
        $field['password'] = \Hash::make('123456');
        $admin = MAdmin::create($field);
        return success_return($admin);
    }

    public function show($id)
    {
        $admin = MAdmin::find($id);
        return success_return($admin);
    }

    public function destroy($id)
    {
        MAdmin::destroy($id);
        return success_return();
    }

    public function role($id)
    {
        $admin = MAdmin::findOrFail($id);
        $admin->role_id = \request('role_id');
        $admin->save();
        return success_return();
    }

    public function resetPassword($id)
    {
        $admin = MAdmin::findOrFail($id);
        $admin->password = \Hash::make(\request('password'));
        $admin->save();
        return success_return();
    }

    /**
     * 将丢弃
     * @param AdminedRequest $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function updatePassword(AdminedRequest $request) {
        $admin = Auth::guard('admin')->user() ?? Auth::guard('m_admin')->user();
        if (!\Hash::check($request->old_password, $admin->password)) {
            return response()->json(['msg' => '原始密码输入错误', 'code' => 5502]);
        } else {
            $admin->password = bcrypt($request->password);
            $admin->save();
            return success_return();
        }
    }
    public function updatePasswordNew() {
        $admin =Auth::guard('m_admin')->user() ?? Auth::guard('admin')->user();
        if (!\Hash::check(\request('old_password'), $admin->password)) {
            return response()->json(['msg' => '原始密码输入错误', 'code' => 5502]);
        } else {
            $admin->password = \Hash::make(\request('password'));
            $admin->save();
            return success_return();
        }
    }
}
