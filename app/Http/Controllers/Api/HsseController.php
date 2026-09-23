<?php

namespace App\Http\Controllers\Api;

use App\Enums\HsseLevel;
use App\Enums\MeetingStatus;
use App\Http\Controllers\Controller;
use App\Models\HsseMeeting;
use Illuminate\Http\Request;

class HsseController extends Controller
{
    public function index(Request $request)
    {
        $query = HsseMeeting::with(['unit', 'zone', 'site']);

        $query->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('zone_id'), fn ($q) => $q->where('zone_id', $request->integer('zone_id')))
            ->when($request->filled('site_id'), fn ($q) => $q->where('site_id', $request->integer('site_id')))
            ->when($request->filled('quarter'), fn ($q) => $q->where('quarter', $request->integer('quarter')))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->integer('level')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));

        $meetings = $query->orderByDesc('year')->orderBy('level')->orderBy('quarter')
            ->paginate($request->integer('per_page', 20));

        $summary = collect([1, 2, 3])->map(function ($level) use ($request, $query) {
            $q = (clone $query)->where('level', $level);

            return [
                'level' => $level,
                'target' => (clone $q)->sum('target_count'),
                'realized' => (clone $q)->where('status', 'done')->count(),
                'missed' => (clone $q)->where('status', 'missed')->count(),
            ];
        });

        return response()->json(['data' => $meetings, 'summary' => $summary]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        return response()->json(['data' => HsseMeeting::create($data)->load(['unit', 'zone', 'site'])], 201);
    }

    public function show(HsseMeeting $meeting)
    {
        return response()->json(['data' => $meeting->load(['unit', 'zone', 'site'])]);
    }

    public function update(Request $request, HsseMeeting $meeting)
    {
        $data = $request->validate($this->rules());

        $meeting->update($data);

        return response()->json(['data' => $meeting->load(['unit', 'zone', 'site'])]);
    }

    public function destroy(HsseMeeting $meeting)
    {
        $meeting->delete();

        return response()->json(['message' => 'Meeting deleted.']);
    }

    protected function rules(): array
    {
        return [
            'level' => 'required|in:'.implode(',', HsseLevel::values()),
            'title' => 'required|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'zone_id' => 'nullable|exists:zones,id',
            'site_id' => 'nullable|exists:sites,id',
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2000|max:2100',
            'target_count' => 'required|integer|min:1',
            'planned_date' => 'nullable|date',
            'realized_date' => 'nullable|date',
            'status' => 'required|in:'.implode(',', MeetingStatus::values()),
            'notes' => 'nullable|string',
        ];
    }
}