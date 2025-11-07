<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roles = $user->roles->pluck('name')->toArray();

        if (in_array('Super Admin', $roles)) {
            return redirect('/admin/modules');
        }

        foreach ($roles as $role) {
            $slug = Str::kebab($role);
            $map = [
                'client' => 'client.dashboard',
                'employee' => 'employee.dashboard',
                'manager' => 'manager.dashboard',
                'admin' => 'manager.dashboard',
            ];

            if (isset($map[$slug])) {
                return redirect()->route($map[$slug]);
            }

            if (view()->exists("dashboards.{$slug}")) {
                return view("dashboards.{$slug}");
            }
        }

        return view('dashboard');
    }

    public function settings()
    {
        return view('admin.settings');
    }
}