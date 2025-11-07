<?php

namespace App\Http\Controllers;

use App\Models\ClientSale;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientSaleController extends Controller
{
  public function index(Request $request)
{
    $sales = ClientSale::with('client')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($sales);
    }

    return view('sales.index', compact('sales'));
}

    public function mySales(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        $sales = $client->sales()->latest()->get();
        return $request->wantsJson()
            ? response()->json($sales)
            : view('client.sales', compact('sales'));
    }

    public function downloadInvoice($invoice)
    {
        $sale = ClientSale::where('invoice_no', $invoice)->firstOrFail();
        // PDF logic later
        return response()->json(['url' => '#']);
    }
}