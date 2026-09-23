<?php

namespace Database\Seeders;

use App\Models\ComplianceItem;
use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\HsseMeeting;
use App\Models\Plan;
use App\Models\PlanTask;
use App\Models\RiskRegister;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::pluck('id', 'code')->all();
        $zones = Zone::pluck('id', 'code')->all();
        $units = Unit::pluck('id', 'code')->all();
        $pics = User::pluck('id')->all();
        $departments = Unit::where('type', 'department')->pluck('id')->all();

        $year = now()->year;

        // ---------- PLANNING ----------
        $planSeed = [
            ['unit' => 'OPR-MNT', 'title' => 'Turnaround Unit 1', 'cat' => 'Maintenance', 'q' => 1, 'status' => 'done', 'progress' => 100],
            ['unit' => 'OPR-MNT', 'title' => 'PM Overhaul Compressor', 'cat' => 'Maintenance', 'q' => 2, 'status' => 'plan', 'progress' => 65],
            ['unit' => 'OPR-PRD', 'title' => 'Optimization Throughput FY26', 'cat' => 'Production', 'q' => 1, 'status' => 'plan', 'progress' => 40],
            ['unit' => 'ENG-PRJ', 'title' => 'Revamp Storage Tank', 'cat' => 'Project', 'q' => 2, 'status' => 'plan', 'progress' => 55],
            ['unit' => 'HSSE-SHE', 'title' => 'Annual SHE Training Program', 'cat' => 'Training', 'q' => 1, 'status' => 'plan', 'progress' => 30],
            ['unit' => 'FIN-BGT', 'title' => 'Rolling Forecast Q2', 'cat' => 'Finance', 'q' => 2, 'status' => 'done', 'progress' => 100],
            ['unit' => 'PRC-CTR', 'title' => 'Vendor Performance Review', 'cat' => 'Procurement', 'q' => 1, 'status' => 'plan', 'progress' => 75],
            ['unit' => 'LGL-CMP', 'title' => 'Regulatory Gap Assessment', 'cat' => 'Compliance', 'q' => 3, 'status' => 'plan', 'progress' => 20],
            ['unit' => 'HRA-PCP', 'title' => 'Talent Succession Pipeline', 'cat' => 'HR', 'q' => 2, 'status' => 'done', 'progress' => 100],
            ['unit' => 'OPR-LOG', 'title' => 'Fleet Route Optimization', 'cat' => 'Logistics', 'q' => 4, 'status' => 'plan', 'progress' => 10],
        ];

        foreach ($planSeed as $p) {
            $plan = Plan::create([
                'unit_id' => $units[$p['unit']],
                'title' => $p['title'],
                'description' => "Monitoring plan ".$p['cat'].' — '.$p['title'],
                'category' => $p['cat'],
                'target_date' => $this->quarterDate($year, $p['q'])->copy()->endOfQuarter(),
                'realized_date' => $p['status'] === 'done' ? $this->quarterDate($year, $p['q'])->addDays(30) : null,
                'status' => $p['status'],
                'progress' => $p['progress'],
                'year' => $year,
                'quarter' => $p['q'],
                'pic_user_id' => $pics[array_rand($pics)],
                'notes' => 'Sample planning data',
            ]);

            if ($plan->status === 'plan') {
                PlanTask::create(['plan_id' => $plan->id, 'name' => 'Kick-off & scoping', 'status' => 'done', 'progress' => 100]);
                PlanTask::create(['plan_id' => $plan->id, 'name' => 'Execution window', 'status' => 'in_progress', 'progress' => intdiv($plan->progress, 2)]);
                PlanTask::create(['plan_id' => $plan->id, 'name' => 'Closure & reporting', 'status' => 'pending', 'progress' => 0]);
            }
        }

        // ---------- RISK ----------
        $riskSeed = [
            ['unit' => 'OPR-MNT', 'site' => 'ST-A1', 'title' => 'Unplanned shutdown of main unit', 'cat' => 'operational', 'l' => 3, 'i' => 5, 'status' => 'mitigated'],
            ['unit' => 'OPR-PRD', 'site' => 'ST-B1', 'title' => 'Throughput loss due to feedstock quality', 'cat' => 'operational', 'l' => 4, 'i' => 4, 'status' => 'monitored'],
            ['unit' => 'HSSE-SHE', 'site' => 'ST-A1', 'title' => 'Major process safety incident', 'cat' => 'environmental', 'l' => 2, 'i' => 5, 'status' => 'assessed'],
            ['unit' => 'FIN-BGT', 'site' => null, 'title' => 'FX volatility impact on budget', 'cat' => 'financial', 'l' => 3, 'i' => 3, 'status' => 'monitored'],
            ['unit' => 'PRC-CTR', 'site' => null, 'title' => 'Single source vendor dependency', 'cat' => 'strategic', 'l' => 4, 'i' => 3, 'status' => 'mitigated'],
            ['unit' => 'LGL-CMP', 'site' => null, 'title' => 'Non-compliance of operating permit', 'cat' => 'compliance', 'l' => 2, 'i' => 5, 'status' => 'identified'],
            ['unit' => 'ENG-PRJ', 'site' => 'ST-C1', 'title' => 'Project delay impact on schedule', 'cat' => 'operational', 'l' => 3, 'i' => 4, 'status' => 'assessed'],
            ['unit' => 'HSSE-SEC', 'site' => 'ST-A2', 'title' => 'Security breach at terminal', 'cat' => 'operational', 'l' => 2, 'i' => 4, 'status' => 'mitigated'],
            ['unit' => 'OPR-LOG', 'site' => 'ST-C2', 'title' => 'Logistics disruption (weather)', 'cat' => 'operational', 'l' => 3, 'i' => 3, 'status' => 'monitored'],
            ['unit' => 'HRA-PCP', 'site' => null, 'title' => 'Critical skill attrition', 'cat' => 'strategic', 'l' => 3, 'i' => 4, 'status' => 'mitigated'],
        ];

        for ($i = 0; $i < count($riskSeed); $i++) {
            $r = $riskSeed[$i];
            RiskRegister::create([
                'risk_code' => 'RK-'.($year % 100).'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'unit_id' => $units[$r['unit']],
                'site_id' => $r['site'] ? $sites[$r['site']] : null,
                'title' => $r['title'],
                'description' => 'Sample risk description.',
                'category' => $r['cat'],
                'likelihood' => $r['l'],
                'impact' => $r['i'],
                'mitigation' => 'Buat kontrol preventif & monitoring berkala',
                'contingency' => 'Rencana kontingensi dan perbaikan',
                'pic_user_id' => $pics[array_rand($pics)],
                'status' => $r['status'],
                'due_date' => now()->addDays(30 + $i * 10),
                'year' => $year,
                'quarter' => (($i % 4) + 1),
                'evidence' => null,
            ]);
        }

        // ---------- OGB / CONTRACTS ----------
        $contractSeed = [
            ['num' => 'ILJ-2026-001', 'name' => 'ILJ Maintenance Contract Plant A', 'type' => 'ilj', 'unit' => 'OPR-MNT', 'site' => 'ST-A1', 'vendor' => 'PT Mitra Jaya', 'val' => 5200000000, 'used' => 2100000000, 'end' => now()->addMonths(8), 'mtype' => 'Preventive & Corrective', 'mp' => 42, 'wp' => 40],
            ['num' => 'ILJ-2026-002', 'name' => 'ILJ Fabrication Support', 'type' => 'ilj', 'unit' => 'ENG-PRJ', 'site' => 'ST-B1', 'vendor' => 'PT Karya Sejahtera', 'val' => 3900000000, 'used' => 2500000000, 'end' => now()->addDays(20), 'mtype' => 'Fabrication', 'mp' => 78, 'wp' => 75],
            ['num' => 'ILJ-2026-003', 'name' => 'ILJ Terminal Operation', 'type' => 'ilj', 'unit' => 'OPR-LOG', 'site' => 'ST-A2', 'vendor' => 'PT Trans Terminal', 'val' => 7800000000, 'used' => 1500000000, 'end' => now()->addMonths(14), 'mtype' => 'Operation & Maintenance', 'mp' => 25, 'wp' => 22],
            ['num' => 'MNT-2025-118', 'name' => 'Rotating Equipment Overhaul', 'type' => 'maintenance', 'unit' => 'OPR-MNT', 'site' => 'ST-A1', 'vendor' => 'PT Turbo Servis', 'val' => 850000000, 'used' => 840000000, 'end' => now()->subDays(10), 'mtype' => 'Overhaul', 'mp' => 100, 'wp' => 98],
            ['num' => 'MNT-2026-045', 'name' => 'Instrument Calibration', 'type' => 'maintenance', 'unit' => 'ENG-TCH', 'site' => 'ST-B2', 'vendor' => 'PT Calido', 'val' => 420000000, 'used' => 120000000, 'end' => now()->addMonths(5), 'mtype' => 'Calibration', 'mp' => 30, 'wp' => 28],
            ['num' => 'GEN-2026-012', 'name' => 'Catering & Housekeeping', 'type' => 'general', 'unit' => 'HRA-GAD', 'site' => 'ST-A1', 'vendor' => 'PT Restu Bunda', 'val' => 1350000000, 'used' => 600000000, 'end' => now()->addMonths(6), 'mtype' => null, 'mp' => 45, 'wp' => 44],
            ['num' => 'GEN-2026-020', 'name' => 'IT Infrastructure Support', 'type' => 'general', 'unit' => 'ENG-TCH', 'site' => null, 'vendor' => 'PT Solusi Data', 'val' => 900000000, 'used' => 450000000, 'end' => now()->addMonths(10), 'mtype' => null, 'mp' => 50, 'wp' => 50],
            ['num' => 'ILJ-2026-004', 'name' => 'ILJ Jetty Maintenance', 'type' => 'ilj', 'unit' => 'OPR-LOG', 'site' => 'ST-C2', 'vendor' => 'PT Marine Prima', 'val' => 2800000000, 'used' => 900000000, 'end' => now()->addMonths(11), 'mtype' => 'Marine Structure', 'mp' => 35, 'wp' => 33],
        ];

        foreach ($contractSeed as $c) {
            $status = $c['end']->lessThan(now()) ? 'expired' : ($c['end']->lte(now()->addDays(60)) ? 'expiring' : 'active');
            $contract = Contract::create([
                'contract_number' => $c['num'],
                'name' => $c['name'],
                'type' => $c['type'],
                'unit_id' => $units[$c['unit']],
                'site_id' => $c['site'] ? $sites[$c['site']] : null,
                'vendor' => $c['vendor'],
                'contract_value' => $c['val'],
                'used_value' => $c['used'],
                'currency' => 'IDR',
                'start_date' => now()->subMonths(8),
                'end_date' => $c['end'],
                'status' => $status,
                'maintenance_type' => $c['mtype'],
                'maintenance_progress' => $c['mp'],
                'remaining_work' => $c['wp'] < 100 ? 'Remaining works in progress' : null,
                'work_progress' => $c['wp'],
                'pic_user_id' => $pics[array_rand($pics)],
                'year' => $year,
                'notes' => 'Sample OGB contract',
            ]);

            ContractMilestone::create(['contract_id' => $contract->id, 'name' => 'Mobilisasi', 'planned_date' => now()->subMonths(6), 'actual_date' => now()->subMonths(5), 'status' => 'reached']);
            ContractMilestone::create(['contract_id' => $contract->id, 'name' => 'Mid-term review', 'planned_date' => now(), 'status' => 'pending']);
            ContractMilestone::create(['contract_id' => $contract->id, 'name' => 'Demobilisasi', 'planned_date' => now()->addMonths(6), 'status' => 'pending']);
        }

        // ---------- HSSE MEETINGS ----------
        $levels = [1, 2, 3];
        foreach ($levels as $level) {
            foreach ($zones as $zCode => $_z) {
                for ($q = 1; $q <= 2; $q++) {
                    $targets = [1 => 4, 2 => 4, 3 => 4][$level];
                    for ($i = 1; $i <= $targets; $i++) {
                        $site = $q === 1 ? $sites['ST-A1'] : $sites['ST-B1'];
                        $done = $i <= ($targets - 1);
                        HsseMeeting::create([
                            'level' => $level,
                            'title' => "HSSE Committee L{$level} Q{$q} - {$zCode}.{$i}",
                            'unit_id' => $units['HSSE-SHE'],
                            'zone_id' => $zones[$zCode],
                            'site_id' => $site,
                            'quarter' => $q,
                            'year' => $year,
                            'target_count' => $targets,
                            'planned_date' => $this->quarterDate($year, $q)->addDays($i * 20),
                            'realized_date' => $done ? $this->quarterDate($year, $q)->addDays($i * 20 - 3) : null,
                            'status' => $done ? 'done' : 'scheduled',
                            'notes' => 'Sample meeting',
                        ]);
                    }
                }
            }
        }

        // ---------- COMPLIANCE ----------
        $compSeed = [
            ['req' => 'Perpanjangan izin usaha (NIU) terkini', 'reg' => 'Peraturan Pemerintah terkait Perizinan', 'cat' => 'legal', 'unit' => 'LGL-CMP', 'status' => 'compliant', 'prog' => 100],
            ['req' => 'Pelaporan wajib pelaporan kepatuhan', 'reg' => 'Regulasi Kepatuhan', 'cat' => 'regulatory', 'unit' => 'LGL-CMP', 'status' => 'in_progress', 'prog' => 60],
            ['req' => 'Audit internal Q2 - closing temuan', 'reg' => 'Standar Internal Audit', 'cat' => 'audit', 'unit' => 'FIN-ACC', 'status' => 'partial', 'prog' => 70],
            ['req' => 'Sertifikasi SMK3 diperbaharui', 'reg' => 'Permenaker SMK3', 'cat' => 'regulatory', 'unit' => 'HSSE-SHE', 'status' => 'in_progress', 'prog' => 45],
            ['req' => 'Pembaruan kontrak vendor kritis', 'reg' => 'Kebijakan Pengadaan', 'cat' => 'internal', 'unit' => 'PRC-CTR', 'status' => 'non_compliant', 'prog' => 20],
            ['req' => 'Survei kepuasan dan kepatuhan SDM', 'reg' => 'Kebijakan SDM', 'cat' => 'internal', 'unit' => 'HRA-PCP', 'status' => 'compliant', 'prog' => 100],
            ['req' => 'Pajak & pelaporan keuangan tepat waktu', 'reg' => 'UU Perpajakan', 'cat' => 'legal', 'unit' => 'FIN-ACC', 'status' => 'compliant', 'prog' => 100],
            ['req' => 'Pengelolaan limbah B3 sesuai aturan', 'reg' => 'UU Lingkungan Hidup', 'cat' => 'regulatory', 'unit' => 'HSSE-SHE', 'status' => 'partial', 'prog' => 75],
            ['req' => 'Pemenuhan tenggat lisensi perangkat lunak', 'reg' => 'Kebijakan IT', 'cat' => 'internal', 'unit' => 'ENG-TCH', 'status' => 'compliant', 'prog' => 100],
            ['req' => 'Dokumen keamanan terminal diperbarui', 'reg' => 'Regulasi Keamanan Terminal', 'cat' => 'regulatory', 'unit' => 'HSSE-SEC', 'status' => 'in_progress', 'prog' => 50],
        ];

        foreach ($compSeed as $i => $c) {
            ComplianceItem::create([
                'requirement' => $c['req'],
                'regulation' => $c['reg'],
                'category' => $c['cat'],
                'unit_id' => $units[$c['unit']],
                'pic_user_id' => $pics[array_rand($pics)],
                'deadline' => now()->addDays(20 + $i * 15),
                'status' => $c['status'],
                'progress' => $c['prog'],
                'year' => $year,
                'quarter' => (($i % 4) + 1),
                'notes' => 'Sample compliance item',
            ]);
        }
    }

    protected function quarterDate(int $year, int $quarter): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::create($year, (($quarter - 1) * 3) + 1, 15);
    }
}