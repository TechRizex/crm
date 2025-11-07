<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientSale;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
   public function index(Request $request)
{
    $data = [
        'total_clients' => Client::count(),
        'total_sales' => ClientSale::sum('amount'),
        'open_tasks' => Task::where('status', '!=', 'Completed')->count()
    ];

    if ($request->wantsJson() || $request->is('api/*')) {
        return response()->json($data);
    }

    return view('reports.index', compact('data'));
}
    public function logs(Request $request)
    {
        $logs = DB::table('activity_logs')
            ->latest()
            ->paginate(50);

        return $request->wantsJson()
            ? response()->json($logs)
            : view('reports.logs', compact('logs'));
    }

    public function managerReports(Request $request)
    {
        $teamSales = ClientSale::whereHas('client.accountManager', function($q) {
            $q->where('id', auth()->id());
        })->sum('total_amount');

        return $request->wantsJson()
            ? response()->json(['team_sales' => $teamSales])
            : view('manager.reports', compact('teamSales'));
    }
}