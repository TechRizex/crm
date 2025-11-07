<?php

namespace App\Http\Controllers;

use App\Models\ClientPurchase;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientPurchaseController extends Controller
{
   public function index(Request $request)
{
    $purchases = ClientPurchase::with('client')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($purchases);
    }

    return view('purchases.index', compact('purchases'));
}

    public function myPurchases(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        $purchases = $client->purchases()->latest()->get();
        return $request->wantsJson()
            ? response()->json($purchases)
            : view('client.purchases', compact('purchases'));
    }
}