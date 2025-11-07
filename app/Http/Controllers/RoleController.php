<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
 public function index(Request $request)
{
    $roles = Role::with('permissions')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($roles);
    }

    return view('roles.index', compact('roles'));
}

    public function create(Request $request)
    {
        $permissions = Permission::all();
        return $request->wantsJson()
            ? response()->json($permissions)
            : view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $role = Role::create($request->validate([
            'name' => 'required|unique:roles',
            'slug' => 'nullable',
            'description' => 'nullable'
        ]));

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $request->wantsJson()
            ? response()->json($role->load('permissions'), 201)
            : redirect()->route('roles.index');
    }

    public function edit(Request $request, Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');
        return $request->wantsJson()
            ? response()->json(compact('role', 'permissions'))
            : view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $role->update($request->validate([
            'name' => 'required|unique:roles,name,' . $role->id
        ]));

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $request->wantsJson()
            ? response()->json($role->load('permissions'))
            : redirect()->route('roles.index');
    }

    public function destroy(Request $request, Role $role)
    {
        $role->delete();
        return $request->wantsJson()
            ? response()->json(null, 204)
            : redirect()->route('roles.index');
    }
}