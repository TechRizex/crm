<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
   public function index(Request $request)
{
    $users = User::with('roles')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($users);
    }

    return view('users.index', compact('users'));
}

    public function create(Request $request)
    {
        $roles = Role::all();
        return $request->wantsJson()
            ? response()->json($roles)
            : view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::create([
            'uuid' => \Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active'
        ]);

        $user->assignRole($data['role']);

        return $request->wantsJson()
            ? response()->json($user->load('roles'), 201)
            : redirect()->route('users.index');
    }

    public function edit(Request $request, User $user)
    {
        $roles = Role::all();
        return $request->wantsJson()
            ? response()->json(compact('user', 'roles'))
            : view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name'
        ]);

        $user->update($data);
        $user->syncRoles($data['role']);

        return $request->wantsJson()
            ? response()->json($user->load('roles'))
            : redirect()->route('users.index');
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();
        return $request->wantsJson()
            ? response()->json(null, 204)
            : redirect()->route('users.index');
    }
}