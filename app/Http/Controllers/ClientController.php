<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
   public function index(Request $request)
{
    $clients = Client::with('user')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($clients);
    }

    return view('clients.index', compact('clients'));
}

    public function create(Request $request)
    {
        $managers = User::role(['Manager', 'Admin'])->get();
        return $request->wantsJson()
            ? response()->json($managers)
            : view('clients.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:clients',
            'account_manager_id' => 'nullable|exists:users,id'
        ]);

        $loginUser = User::create([
            'uuid' => Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(12)),
            'status' => 'active'
        ]);

        $loginUser->assignRole('Client');

        $client = Client::create([
            'uuid' => Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'account_manager_id' => $data['account_manager_id'],
            'user_id' => $loginUser->id,
            'status' => 'active'
        ]);

        return $request->wantsJson()
            ? response()->json($client->load('user'), 201)
            : redirect()->route('clients.index');
    }

    public function dashboard(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        $sales = $client->sales()->latest()->take(5)->get();
        $tickets = $client->tickets()->latest()->take(5)->get();

        return $request->wantsJson()
            ? response()->json(compact('client', 'sales', 'tickets'))
            : view('client.dashboard', compact('client', 'sales', 'tickets'));
    }

    public function profile(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        return $request->wantsJson()
            ? response()->json($client)
            : view('client.profile', compact('client'));
    }

    public function updateProfile(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        $client->update($request->only(['name', 'phone', 'address']));
        return $request->wantsJson()
            ? response()->json($client)
            : redirect()->back();
    }
}