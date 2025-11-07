<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
  public function index(Request $request)
{
    $leads = Lead::with('assignedTo')->paginate(15);

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($leads);
    }

    return view('leads.index', compact('leads'));
}

    public function myLeads(Request $request)
    {
        $leads = auth()->user()->leads()->latest()->get();
        return $request->wantsJson()
            ? response()->json($leads)
            : view('employee.leads', compact('leads'));
    }
}