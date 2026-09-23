<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplianceCategory;
use App\Enums\ComplianceStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplianceEvidence;
use App\Models\ComplianceItem;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function index(Request $request)
    {
        $query = ComplianceItem::with(['unit', 'pic']);

        $query->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('quarter'), fn ($q) => $q->where('quarter', $request->integer('quarter')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->boolean('overdue'), fn ($q) => $q->where('status', '!=', 'compliant')->where('deadline', '<', now()))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('requirement', 'like', '%'.$request->input('search').'%')
                    ->orWhere('regulation', 'like', '%'.$request->input('search').'%');
            }));

        $items = $query->orderByDesc('year')->orderBy('deadline')->paginate($request->integer('per_page', 20));

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        return response()->json(['data' => ComplianceItem::create($data)->load(['unit', 'pic'])], 201);
    }

    public function show(ComplianceItem $item)
    {
        return response()->json(['data' => $item->load(['unit', 'pic', 'evidence.uploader'])]);
    }

    public function update(Request $request, ComplianceItem $item)
    {
        $data = $request->validate($this->rules());

        $item->update($data);

        return response()->json(['data' => $item->load(['unit', 'pic'])]);
    }

    public function destroy(ComplianceItem $item)
    {
        $item->delete();

        return response()->json(['message' => 'Compliance item deleted.']);
    }

    protected function rules(): array
    {
        return [
            'requirement' => 'required|string|max:255',
            'regulation' => 'nullable|string|max:160',
            'category' => 'required|in:'.implode(',', ComplianceCategory::values()),
            'unit_id' => 'nullable|exists:units,id',
            'pic_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'status' => 'required|in:'.implode(',', ComplianceStatus::values()),
            'progress' => 'nullable|integer|min:0|max:100',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'notes' => 'nullable|string',
        ];
    }

    public function storeEvidence(Request $request, ComplianceItem $item)
    {
        $data = $request->validate([
            'evidence' => 'required|file|max:10240|mimes:pdf,png,jpg,jpeg,doc,docx,xls,xlsx,zip',
        ]);

        $file = $request->file('evidence');
        $path = $file->store('compliance/'.$item->id, 'public');

        $evidence = ComplianceEvidence::create([
            'compliance_item_id' => $item->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['data' => $evidence->load('uploader')], 201);
    }

    public function destroyEvidence(ComplianceItem $item, ComplianceEvidence $evidence)
    {
        abort_if($evidence->compliance_item_id !== $item->id, 404);
        $evidence->delete();

        return response()->json(['message' => 'Evidence deleted.']);
    }
}