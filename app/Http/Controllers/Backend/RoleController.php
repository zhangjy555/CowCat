<?php

namespace App\Http\Controllers\Backend;

use App\Facades\RoleRepository;
use App\Http\Requests\Form\RoleCreateForm;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 角色管理控制器
 *
 * @package App\Http\Controllers\Backend
 */
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = RoleRepository::paginate(config('repository.page-limit'));

        return view('backend.role.index', compact("data"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.role.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Form\RoleCreateForm $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(RoleCreateForm $request)
    {
        try {
            if (RoleRepository::create($request->all())) {
                return redirect()->route("role.index")->withSuccess("新增角色成功");
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(array('error' => $e->getMessage()))->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = RoleRepository::find($id);

        return view('backend.role.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Form\RoleCreateForm $request
     * @param  int                                    $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(RoleCreateForm $request, $id)
    {
        $role = RoleRepository::find($id);
        $role->name = $request['name'];
        $role->description = $request['description'];
        $role->display_name = $request['display_name'];

        try {
            if ($role->save()) {
                return redirect()->back()->withSuccess("编辑角色成功");
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(array('error' => $e->getMessage()))->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (RoleRepository::destroy($id)) {
                return redirect()->back()->withSuccess("删除角色成功");
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(array('error' => $e->getMessage()));
        }
    }

    /**
     * 角色赋权页面
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function permission($id)
    {
        $role = RoleRepository::find($id);
        $data = json_encode(RoleRepository::getTypeGroupPermissionsByRole($role));

        return view('backend.role.permission', compact('id', 'data', 'role'));
    }


    /**
     * 角色赋权操作
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function associatePermission(Request $request)
    {
        $id = $request['id'];
        $permissions = $request['permissions'];

        $data = [];
        foreach ($permissions as $permission) {
            if ( ! $permission) {
                continue;
            }

            if (array_key_exists($permission, config('cowcat.permission-type'))) {
                continue;
            }

            $data[] = $permission;
        }

        try {
            $role = RoleRepository::find($id);
            if ($role->perms()->sync($data)) {
                return $this->responseJson(['status' => 1]);
            } else {
                return $this->responseJson(['status' => 0, 'message' => '角色赋权失败']);
            }
        } catch (\Exception $e) {
            return $this->responseJson(['status' => 0, 'message' => $e->getMessage()]);
        }
    }
}