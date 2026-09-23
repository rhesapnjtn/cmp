<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanTask;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::with(['unit', 'pic'])->withCount('tasks');

        $query->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('quarter'), fn ($q) => $q->where('quarter', $request->integer('quarter')))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->input('search').'%')
                    ->orWhere('category', 'like', '%'.$request->input('search').'%')
                    ->orWhereHas('unit', fn ($u) => $u->where('name', 'like', '%'.$request->input('search').'%'));
            }))
            ->when($request->boolean('unrealized'), fn ($q) => $q->where('status', '=', 'plan')->where('target_date', '<', now()));

        $plans = $query->orderByDesc('year')->orderBy('target_date')->paginate($request->integer('per_page', 20));

        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:80',
            'target_date' => 'nullable|date',
            'realized_date' => 'nullable|date',
            'status' => 'required|in:'.implode(',', PlanStatus::values()),
            'progress' => 'nullable|integer|min:0|max:100',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'pic_user_id' => 'nullable|exists:users,id',
            'evidence' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $plan = Plan::create($data);

        return response()->json(['data' => $plan->load(['unit', 'pic'])], 201);
    }

    public function show(Plan $plan)
    {
        return response()->json(['data' => $plan->load(['unit', 'pic', 'tasks'])->loadCount('tasks')]);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'unit_id' => 'sometimes|exists:units,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:80',
            'target_date' => 'nullable|date',
            'realized_date' => 'nullable|date',
            'status' => 'sometimes|in:'.implode(',', PlanStatus::values()),
            'progress' => 'nullable|integer|min:0|max:100',
            'year' => 'sometimes|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'pic_user_id' => 'nullable|exists:users,id',
            'evidence' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $plan->update($data);

        return response()->json(['data' => $plan->load(['unit', 'pic'])]);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json(['message' => 'Plan deleted.']);
    }

    public function indexTasks(Plan $plan)
    {
        return response()->json(['data' => $plan->tasks()->orderBy('id')->get()]);
    }

    public function storeTask(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'target_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $task = $plan->tasks()->create($data);

        return response()->json(['data' => $task], 201);
    }

    public function updateTask(Request $request, Plan $plan, PlanTask $task)
    {
        abort_if($task->plan_id !== $plan->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'target_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,in_progress,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $task->update($data);

        return response()->json(['data' => $task]);
    }

    public function destroyTask(Plan $plan, PlanTask $task)
    {
        abort_if($task->plan_id !== $plan->id, 404);
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }
}