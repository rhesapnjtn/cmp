<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContractType;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractMilestone;
use Illuminate\Http\Request;

class OgbController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['unit', 'site.zone', 'pic']);

        $query->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('site_id'), fn ($q) => $q->where('site_id', $request->integer('site_id')))
            ->when($request->filled('zone_id'), fn ($q) => $q->whereHas('site', fn ($s) => $s->where('zone_id', $request->integer('zone_id'))))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('contract_number', 'like', '%'.$request->input('search').'%')
                    ->orWhere('vendor', 'like', '%'.$request->input('search').'%');
            }));

        $contracts = $query->orderByDesc('year')->orderByDesc('end_date')->paginate($request->integer('per_page', 20));

        return response()->json($contracts);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $contract = Contract::create($data);

        return response()->json(['data' => $contract->load(['unit', 'site.zone', 'pic'])], 201);
    }

    public function show(Contract $contract)
    {
        return response()->json(['data' => $contract->load(['unit', 'site.zone', 'pic', 'milestones'])]);
    }

    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate($this->rules($contract->id));

        $contract->update($data);

        return response()->json(['data' => $contract->load(['unit', 'site.zone', 'pic'])]);
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return response()->json(['message' => 'Contract deleted.']);
    }

    protected function rules(?int $id = null): array
    {
        return [
            'contract_number' => 'required|string|max:80|unique:contracts,contract_number'.($id ? ",$id" : ''),
            'name' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', ContractType::values()),
            'unit_id' => 'nullable|exists:units,id',
            'site_id' => 'nullable|exists:sites,id',
            'vendor' => 'nullable|string|max:255',
            'contract_value' => 'required|numeric|min:0',
            'used_value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'required|in:active,expiring,expired,completed,terminated',
            'maintenance_type' => 'nullable|string|max:60',
            'maintenance_progress' => 'nullable|integer|min:0|max:100',
            'remaining_work' => 'nullable|string',
            'work_progress' => 'nullable|integer|min:0|max:100',
            'pic_user_id' => 'nullable|exists:users,id',
            'year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string',
        ];
    }

    public function indexMilestones(Contract $contract)
    {
        return response()->json(['data' => $contract->milestones()->orderBy('planned_date')->get()]);
    }

    public function storeMilestone(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'planned_date' => 'nullable|date',
            'actual_date' => 'nullable|date',
            'status' => 'required|in:pending,reached,missed',
            'notes' => 'nullable|string',
        ]);

        return response()->json(['data' => $contract->milestones()->create($data)], 201);
    }

    public function updateMilestone(Request $request, Contract $contract, ContractMilestone $milestone)
    {
        abort_if($milestone->contract_id !== $contract->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'planned_date' => 'nullable|date',
            'actual_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,reached,missed',
            'notes' => 'nullable|string',
        ]);

        $milestone->update($data);

        return response()->json(['data' => $milestone]);
    }

    public function destroyMilestone(Contract $contract, ContractMilestone $milestone)
    {
        abort_if($milestone->contract_id !== $contract->id, 404);
        $milestone->delete();

        return response()->json(['message' => 'Milestone deleted.']);
    }
}