<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Client;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
{
    $tickets = Ticket::with('client')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($tickets);
    }

    return view('tickets.index', compact('tickets'));
}

    public function myTickets(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();
        $tickets = $client->tickets()->latest()->get();
        return $request->wantsJson()
            ? response()->json($tickets)
            : view('client.tickets', compact('tickets'));
    }

    public function create(Request $request)
    {
        return $request->wantsJson()
            ? response()->json(['message' => 'POST to store'])
            : view('client.tickets_create');
    }

    public function store(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->firstOrFail();

        $ticket = Ticket::create([
            'client_id' => $client->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'category' => $request->category ?? 'General',
            'priority' => $request->priority ?? 'Medium',
            'status' => 'Open'
        ]);

        return $request->wantsJson()
            ? response()->json($ticket, 201)
            : redirect()->route('client.tickets');
    }
}