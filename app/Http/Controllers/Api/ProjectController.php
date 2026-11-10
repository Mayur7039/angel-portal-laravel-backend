<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;

class ProjectController extends Controller
{
    public function index()
    {
        // Eager load creator and tasks to avoid N+1 queries
        $projects = Project::with(['creator', 'tasks.assignedUser'])->get();
        return response()->json(['status' => 'success', 'data' => $projects]);
    }

    public function store(StoreProjectRequest $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['status' => 'success', 'data' => $project], 201);
    }
}
