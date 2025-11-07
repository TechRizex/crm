<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use App\Models\Module;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
 public function index(Request $request)
{
    $permissions = Permission::with('module')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($permissions);
    }

    return view('permissions.index', compact('permissions'));
}

    public function create(Request $request)
    {
        $modules = Module::all();
        return $request->wantsJson()
            ? response()->json($modules)
            : view('permissions.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:permissions',
            'module_id' => 'required|exists:modules,id'
        ]);

        $permission = Permission::create($data + ['guard_name' => 'web']);

        return $request->wantsJson()
            ? response()->json($permission, 201)
            : redirect()->route('permissions.index');
    }

    public function destroy(Request $request, Permission $permission)
    {
        $permission->delete();
        return $request->wantsJson()
            ? response()->json(null, 204)
            : redirect()->route('permissions.index');
    }
}