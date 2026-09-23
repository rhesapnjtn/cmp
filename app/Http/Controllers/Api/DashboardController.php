<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComplianceItem;
use App\Models\Contract;
use App\Models\HsseMeeting;
use App\Models\Plan;
use App\Models\RiskRegister;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $unitId = $request->integer('unit_id');
        $zoneId = $request->integer('zone_id');
        $siteId = $request->integer('site_id');
        $quarter = $request->integer('quarter');

        return response()->json([
            'filters' => ['year' => $year, 'unit_id' => $unitId ?: null, 'zone_id' => $zoneId ?: null, 'site_id' => $siteId ?: null, 'quarter' => $quarter ?: null],
            'planning' => $this->planning($year, $unitId, $quarter),
            'risk' => $this->risk($year, $unitId, $zoneId, $siteId, $quarter),
            'ogb' => $this->ogb($year, $unitId, $zoneId, $siteId),
            'hsse' => $this->hsse($year, $unitId, $zoneId, $siteId),
            'compliance' => $this->compliance($year, $unitId, $quarter),
            'trends' => $this->trends($year, $unitId, $zoneId, $siteId),
        ]);
    }

    protected function planning(int $year, int $unitId, int $quarter): array
    {
        $query = Plan::where('year', $year);
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($quarter) {
            $query->where('quarter', $quarter);
        }

        $total = (clone $query)->count();
        $done = (clone $query)->where('status', 'done')->count();
        $planStatus = (clone $query)->where('status', 'plan')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $overdue = (clone $query)->where('status', '!=', 'done')->where('target_date', '<', now())->count();
        $avgProgress = round((clone $query)->avg('progress') ?? 0, 1);

        $byStatus = (clone $query)->selectRaw('status, count(*) as total')
            ->groupBy('status')->get()->map(fn ($r) => ['status' => $r->status, 'total' => $r->total])->all();

        $byUnit = (clone $query)->selectRaw('unit_id, count(*) as total, round(avg(progress)) as progress')
            ->with('unit:id,name')
            ->groupBy('unit_id')->orderByDesc('total')->limit(8)->get()
            ->map(fn ($r) => ['unit_id' => $r->unit_id, 'unit' => $r->unit?->name, 'total' => $r->total, 'progress' => $r->progress])->all();

        return compact('total', 'done', 'planStatus', 'cancelled', 'overdue', 'avgProgress', 'byStatus', 'byUnit');
    }

    protected function risk(int $year, int $unitId, int $zoneId, int $siteId, int $quarter): array
    {
        $query = RiskRegister::where('year', $year);
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        } elseif ($zoneId) {
            $query->whereHas('site', fn ($s) => $s->where('zone_id', $zoneId));
        }
        if ($quarter) {
            $query->where('quarter', $quarter);
        }

        $total = (clone $query)->count();
        $closed = (clone $query)->where('status', 'closed')->count();
        $open = max($total - $closed, 0);

        $byLevel = (clone $query)->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')->get()
            ->mapWithKeys(fn ($r) => [$r->risk_level => $r->total])->all();
        $critical = $byLevel['critical'] ?? 0;
        $high = $byLevel['high'] ?? 0;
        $medium = $byLevel['medium'] ?? 0;
        $low = $byLevel['low'] ?? 0;

        $byCategory = (clone $query)->selectRaw('category, count(*) as total')
            ->groupBy('category')->get()->map(fn ($r) => ['category' => $r->category, 'total' => $r->total])->all();

        $topRisks = (clone $query)->orderByDesc('risk_score')->limit(5)
            ->get(['title', 'risk_code', 'risk_level', 'risk_score', 'status']);

        return compact('total', 'open', 'closed', 'critical', 'high', 'medium', 'low', 'byLevel', 'byCategory', 'topRisks');
    }

    protected function ogb(int $year, int $unitId, int $zoneId, int $siteId): array
    {
        $query = Contract::where('year', $year);
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        } elseif ($zoneId) {
            $query->whereHas('site', fn ($s) => $s->where('zone_id', $zoneId));
        }

        $total = (clone $query)->count();
        $totalValue = (clone $query)->sum('contract_value');
        $usedValue = (clone $query)->sum('used_value');
        $remainingValue = $totalValue - $usedValue;

        $active = (clone $query)->where('status', 'active')->count();
        $expiring = (clone $query)->where('status', 'expiring')->count();
        $expired = (clone $query)->where('status', 'expired')->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $avgWorkProgress = round((clone $query)->avg('work_progress') ?? 0, 1);

        $byStatus = (clone $query)->selectRaw('status, count(*) as total')
            ->groupBy('status')->get()->map(fn ($r) => ['status' => $r->status, 'total' => $r->total])->all();

        $expiringSoon = (clone $query)->whereIn('status', ['active', 'expiring'])->orderBy('end_date')->limit(5)
            ->get(['contract_number', 'name', 'vendor', 'end_date', 'remaining_work', 'work_progress', 'status']);

        return compact('total', 'totalValue', 'usedValue', 'remainingValue', 'active', 'expiring', 'expired', 'completed', 'avgWorkProgress', 'byStatus', 'expiringSoon');
    }

    protected function hsse(int $year, int $unitId, int $zoneId, int $siteId): array
    {
        $query = HsseMeeting::where('year', $year);
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        } elseif ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        $total = (clone $query)->count();
        $done = (clone $query)->where('status', 'done')->count();
        $scheduled = (clone $query)->where('status', 'scheduled')->count();
        $missed = (clone $query)->where('status', 'missed')->count();
        $pt = round($total > 0 ? ($done / $total) * 100 : 0, 1);

        $byLevel = collect([1, 2, 3])->map(function ($level) use ($query) {
            $q = (clone $query)->where('level', $level);
            $target = (clone $q)->sum('target_count');
            $realized = (clone $q)->where('status', 'done')->count();

            return [
                'level' => $level,
                'target' => $target,
                'realized' => $realized,
                'pct' => $target > 0 ? round($realized / $target * 100, 1) : 0,
            ];
        })->all();

        $byQuarter = collect([1, 2, 3, 4])->map(function ($qur) use ($query) {
            $q = (clone $query)->where('quarter', $qur);
            $target = (clone $q)->sum('target_count');
            $realized = (clone $q)->where('status', 'done')->count();

            return ['quarter' => $qur, 'target' => $target, 'realized' => $realized];
        })->all();

        return compact('total', 'done', 'scheduled', 'missed', 'pt', 'byLevel', 'byQuarter');
    }

    protected function compliance(int $year, int $unitId, int $quarter): array
    {
        $query = ComplianceItem::where('year', $year);
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($quarter) {
            $query->where('quarter', $quarter);
        }

        $total = (clone $query)->count();
        $compliant = (clone $query)->where('status', 'compliant')->count();
        $partial = (clone $query)->where('status', 'partial')->count();
        $nonCompliant = (clone $query)->where('status', 'non_compliant')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $overdue = (clone $query)->whereIn('status', ['partial', 'non_compliant', 'in_progress'])->where('deadline', '<', now())->count();
        $rate = $total > 0 ? round($compliant / $total * 100, 1) : 0;

        $byStatus = (clone $query)->selectRaw('status, count(*) as total')
            ->groupBy('status')->get()->map(fn ($r) => ['status' => $r->status, 'total' => $r->total])->all();

        return compact('total', 'compliant', 'partial', 'nonCompliant', 'inProgress', 'overdue', 'rate', 'byStatus');
    }

    protected function trends(int $year, int $unitId, int $zoneId, int $siteId): array
    {
        $quarters = [1, 2, 3, 4];

        $plans = collect($quarters)->map(function ($q) use ($year, $unitId) {
            $query = Plan::where('year', $year)->where('quarter', $q);
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }

            return ['quarter' => $q, 'total' => (clone $query)->count(), 'done' => (clone $query)->where('status', 'done')->count()];
        });

        $risks = collect($quarters)->map(function ($q) use ($year, $unitId, $zoneId, $siteId) {
            $query = RiskRegister::where('year', $year)->where('quarter', $q);
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
            if ($siteId) {
                $query->where('site_id', $siteId);
            } elseif ($zoneId) {
                $query->whereHas('site', fn ($s) => $s->where('zone_id', $zoneId));
            }

            return ['quarter' => $q, 'total' => (clone $query)->count(), 'high_risk' => (clone $query)->where('risk_level', 'high')->count(), 'critical' => (clone $query)->where('risk_level', 'critical')->count()];
        });

        $hsse = collect($quarters)->map(function ($q) use ($year, $unitId, $zoneId, $siteId) {
            $query = HsseMeeting::where('year', $year)->where('quarter', $q);
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
            if ($siteId) {
                $query->where('site_id', $siteId);
            } elseif ($zoneId) {
                $query->where('zone_id', $zoneId);
            }

            return ['quarter' => $q, 'target' => (clone $query)->sum('target_count'), 'done' => (clone $query)->where('status', 'done')->count()];
        });

        $compliance = collect($quarters)->map(function ($q) use ($year, $unitId) {
            $query = ComplianceItem::where('year', $year)->where('quarter', $q);
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }

            return ['quarter' => $q, 'total' => (clone $query)->count(), 'compliant' => (clone $query)->where('status', 'compliant')->count()];
        });

        $contractByValue = collect($quarters)->map(function ($q) use ($year, $unitId, $zoneId, $siteId) {
            $query = Contract::where('year', $year);
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
            if ($siteId) {
                $query->where('site_id', $siteId);
            } elseif ($zoneId) {
                $query->whereHas('site', fn ($s) => $s->where('zone_id', $zoneId));
            }
            $created = collect((clone $query)->get())->filter(fn ($c) => $c->created_at->quarter === $q);

            return ['quarter' => $q, 'value' => $created->sum('contract_value'), 'used' => $created->sum('used_value'), 'active' => $created->where('status', 'active')->count()];
        });

        return compact('plans', 'risks', 'hsse', 'compliance', 'contractByValue');
    }
}