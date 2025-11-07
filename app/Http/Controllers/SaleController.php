<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Sale;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    /**
     * Convert Deal to Sale (Admin / Manager)
     */
    public function store(Request $request, Deal $deal)
    {
        // Check if already sold
        if ($deal->status === 'Won') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Deal already converted'], 400);
            }
            return back()->withErrors(['deal' => 'Deal already converted to sale']);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,Bank Transfer,Card,UPI',
            'notes' => 'nullable|string'
        ]);

        $sale = Sale::create([
            'deal_id' => $deal->id,
            'client_id' => $deal->client_id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'],
            'sold_by' => Auth::id(),
            'sold_at' => now()
        ]);

        // Update Deal Status
        $deal->update(['status' => 'Won']);

        if ($request->wantsJson()) {
            return response()->json($sale->load(['deal', 'client']), 201);
        }

        return redirect()->route('deals.show', $deal)->with('success', 'Sale created!');
    }

    /**
     * Reverse Sale (Cancel)
     */
    public function reverse(Request $request, Sale $sale)
    {
        if ($sale->reversed_at) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Already reversed'], 400);
            }
            return back()->withErrors(['sale' => 'Sale already reversed']);
        }

        $sale->update([
            'reversed_at' => now(),
            'reversed_by' => Auth::id()
        ]);

        // Revert Deal
        $sale->deal->update(['status' => 'Negotiation']);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Sale reversed']);
        }

        return back()->with('success', 'Sale reversed!');
    }

    /**
     * Show Sale Details
     */
    public function show(Request $request, Sale $sale)
    {
        $sale->load(['deal.client', 'soldBy', 'reversedBy']);

        if ($request->wantsJson()) {
            return response()->json($sale);
        }

        return view('sales.show', compact('sale'));
    }

    /**
     * Download Invoice PDF
     */
    public function downloadInvoice(Request $request, Sale $sale)
    {
        if ($sale->reversed_at) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot generate invoice for reversed sale'], 400);
            }
            abort(403);
        }

        $pdf = Pdf::loadView('sales.invoice', compact('sale'));

        if ($request->wantsJson()) {
            $path = 'invoices/sale-' . $sale->id . '.pdf';
            $pdf->save(storage_path('app/public/' . $path));
            return response()->json([
                'download_url' => asset('storage/' . $path)
            ]);
        }

        return $pdf->download('invoice-' . $sale->id . '.pdf');
    }

    /**
     * List All Sales (Admin)
     */
    public function index(Request $request)
    {
        $sales = Sale::with(['client', 'deal'])
            ->whereNull('reversed_at')
            ->orderBy('sold_at', 'desc')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        return view('sales.index', compact('sales'));
    }

    /**
     * Client's Sales (Client Portal)
     */
    public function clientSales(Request $request)
    {
        $sales = Sale::with('deal')
            ->where('client_id', Auth::id())
            ->whereNull('reversed_at')
            ->orderBy('sold_at', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($sales);
        }

        return view('client.sales', compact('sales'));
    }
}