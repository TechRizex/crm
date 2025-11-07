<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
   public function index(Request $request)
{
    $deals = Deal::with('lead', 'client')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($deals);
    }

    return view('deals.index', compact('deals'));
}
}