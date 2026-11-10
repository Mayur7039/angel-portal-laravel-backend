<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Regular members view ONLY their assigned tasks. Admins view all.
        $query = Task::with(['project', 'assignedUser']);

        if ($user->role === 'user') {
            $query->where('assigned_to', $user->id);
        }

        return response()->json(['status' => 'success', 'data' => $query->get()]);
    }

    public function store(StoreTaskRequest $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $task = Task::create($request->validated());

        return response()->json(['status' => 'success', 'data' => $task->load(['project', 'assignedUser'])], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $task = Task::findOrFail($id);
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'due_date' => 'required|date',
        ]);

        $task->update($validated);

        return response()->json(['status' => 'success', 'data' => $task->load(['project', 'assignedUser'])]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['status' => 'success', 'message' => 'Task deleted successfully']);
    }

    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if ($user->role === 'user' && $task->assigned_to !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate(['status' => 'required|in:TODO,WIP,DONE,OVERDUE']);

        // Overdue task logic rules
        if ($task->status === 'OVERDUE' && $request->status === 'WIP') {
            return response()->json(['status' => 'error', 'message' => 'Overdue tasks cannot move back to WIP.'], 422);
        }

        if ($task->status === 'OVERDUE' && $request->status === 'DONE' && $user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can close overdue tasks.'], 403);
        }

        $task->update(['status' => $request->status]);

        return response()->json(['status' => 'success', 'data' => $task]);
    }
}
