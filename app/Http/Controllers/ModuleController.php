<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::with('permissions')->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($modules);
        }

        return view('admin.modules.index', compact('modules'));
    }

    public function create(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Use POST to create']);
        }
        return view('admin.modules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:modules',
            'slug' => 'required|unique:modules',
            'description' => 'nullable'
        ]);

        $module = Module::create($data);

        if ($request->wantsJson()) {
            return response()->json($module->load('permissions'), 201);
        }

        return redirect()->route('admin.modules.index')->with('success', 'Module created!');
    }

    public function show(Request $request, Module $module)
    {
        $module->load('permissions');

        if ($request->wantsJson()) {
            return response()->json($module);
        }

        return view('admin.modules.show', compact('module'));
    }

    public function edit(Request $request, Module $module)
    {
        if ($request->wantsJson()) {
            return response()->json($module);
        }
        return view('admin.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $data = $request->validate([
            'name' => 'required|unique:modules,name,' . $module->id,
            'slug' => 'required|unique:modules,slug,' . $module->id,
            'description' => 'nullable'
        ]);

        $module->update($data);

        if ($request->wantsJson()) {
            return response()->json($module);
        }

        return redirect()->route('admin.modules.index');
    }

    public function destroy(Request $request, Module $module)
    {
        $module->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.modules.index');
    }
}