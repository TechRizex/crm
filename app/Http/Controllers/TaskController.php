<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks (Admin / Manager)
     */
    public function index(Request $request)
    {
        $query = Task::with(['assignedTo', 'deal', 'deal.client']);

        // Super Admin sees all
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('assigned_to', Auth::id());
        }

        $tasks = $query->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($tasks);
        }

        // SIRF tasks.index — No admin.
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show form to create new task
     */
    public function create(Request $request)
    {
        $deals = Deal::with('client')->get();
        $users = User::role(['Manager', 'Employee'])->get();

        if ($request->wantsJson()) {
            return response()->json([
                'deals' => $deals,
                'users' => $users
            ]);
        }

        return view('tasks.create', compact('deals', 'users'));
    }

    /**
     * Store new task
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deal_id' => 'nullable|exists:deals,id',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today',
            'priority' => 'required|in:Low,Medium,High,Urgent'
        ]);

        $data['created_by'] = Auth::id();
        $data['status'] = 'Pending';

        $task = Task::create($data);

        if ($request->wantsJson()) {
            return response()->json($task->load(['assignedTo', 'deal']), 201);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created!');
    }

    /**
     * Show single task
     */
    public function show(Request $request, Task $task)
    {
        $task->load(['assignedTo', 'deal.client', 'createdBy']);

        if ($request->wantsJson()) {
            return response()->json($task);
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Edit task form
     */
    public function edit(Request $request, Task $task)
    {
        $deals = Deal::with('client')->get();
        $users = User::role(['Manager', 'Employee'])->get();

        if ($request->wantsJson()) {
            return response()->json([
                'task' => $task,
                'deals' => $deals,
                'users' => $users
            ]);
        }

        return view('tasks.edit', compact('task', 'deals', 'users'));
    }

    /**
     * Update task
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deal_id' => 'nullable|exists:deals,id',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'sometimes|in:Pending,In Progress,Completed'
        ]);

        $task->update($data);

        if ($request->wantsJson()) {
            return response()->json($task->load(['assignedTo', 'deal']));
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated!');
    }

    /**
     * Delete task
     */
    public function destroy(Request $request, Task $task)
    {
        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted!');
    }

    /**
     * Mark task as complete
     */
    public function complete(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $task->update(['status' => 'Completed']);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task completed', 'task' => $task]);
        }

        return redirect()->back()->with('success', 'Task completed!');
    }

    /**
     * My Tasks (Employee)
     */
    public function myTasks(Request $request)
    {
        $tasks = Task::with(['deal.client'])
            ->where('assigned_to', Auth::id())
            ->orderBy('due_date')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($tasks);
        }

        return view('employee.tasks', compact('tasks'));
    }

    /**
     * Assigned Tasks (Manager)
     */
    public function assigned(Request $request)
    {
        $tasks = Task::with(['assignedTo', 'deal'])
            ->whereIn('assigned_to', User::role('Employee')->pluck('id'))
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($tasks);
        }

        return view('manager.tasks', compact('tasks'));
    }
}