<?php

namespace App\Http\Controllers\Api;

use App\Enums\RiskCategory;
use App\Enums\RiskStatus;
use App\Http\Controllers\Controller;
use App\Models\RiskRegister;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function index(Request $request)
    {
        $query = RiskRegister::with(['unit', 'site.zone', 'pic']);

        $query->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('site_id'), fn ($q) => $q->where('site_id', $request->integer('site_id')))
            ->when($request->filled('zone_id'), fn ($q) => $q->whereHas('site', fn ($s) => $s->where('zone_id', $request->integer('zone_id'))))
            ->when($request->filled('quarter'), fn ($q) => $q->where('quarter', $request->integer('quarter')))
            ->when($request->filled('level'), fn ($q) => $q->where('risk_level', $request->input('level')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->input('search').'%')
                    ->orWhere('risk_code', 'like', '%'.$request->input('search').'%');
            }));

        $risks = $query->orderByDesc('risk_score')->paginate($request->integer('per_page', 20));

        return response()->json($risks);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $risk = RiskRegister::create($data);

        return response()->json(['data' => $risk->load(['unit', 'site.zone', 'pic'])], 201);
    }

    public function show(RiskRegister $risk)
    {
        return response()->json(['data' => $risk->load(['unit', 'site.zone', 'pic'])]);
    }

    public function update(Request $request, RiskRegister $risk)
    {
        $data = $request->validate($this->rules($risk->id));

        $risk->update($data);

        return response()->json(['data' => $risk->load(['unit', 'site.zone', 'pic'])]);
    }

    public function destroy(RiskRegister $risk)
    {
        $risk->delete();

        return response()->json(['message' => 'Risk deleted.']);
    }

    protected function rules(?int $id = null): array
    {
        return [
            'risk_code' => 'required|string|max:50|unique:risk_registers,risk_code'.($id ? ",$id" : ''),
            'unit_id' => 'nullable|exists:units,id',
            'site_id' => 'nullable|exists:sites,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:'.implode(',', RiskCategory::values()),
            'likelihood' => 'required|integer|min:1|max:5',
            'impact' => 'required|integer|min:1|max:5',
            'mitigation' => 'nullable|string',
            'contingency' => 'nullable|string',
            'pic_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:'.implode(',', RiskStatus::values()),
            'due_date' => 'nullable|date',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'evidence' => 'nullable|string',
        ];
    }
}