<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $functions = [
            ['code' => 'OPR', 'name' => 'Operation'],
            ['code' => 'ENG', 'name' => 'Engineering'],
            ['code' => 'HSSE', 'name' => 'HSSE'],
            ['code' => 'FIN', 'name' => 'Finance'],
            ['code' => 'HRA', 'name' => 'HR & Administration'],
            ['code' => 'PRC', 'name' => 'Procurement'],
            ['code' => 'LGL', 'name' => 'Legal & Compliance'],
        ];

        $departments = [
            'OPR' => [
                ['code' => 'OPR-MNT', 'name' => 'Maintenance'],
                ['code' => 'OPR-PRD', 'name' => 'Production'],
                ['code' => 'OPR-LOG', 'name' => 'Logistics'],
            ],
            'ENG' => [
                ['code' => 'ENG-PRJ', 'name' => 'Projects'],
                ['code' => 'ENG-TCH', 'name' => 'Technical'],
            ],
            'HSSE' => [
                ['code' => 'HSSE-SHE', 'name' => 'Safety, Health & Environment'],
                ['code' => 'HSSE-SEC', 'name' => 'Security'],
            ],
            'FIN' => [
                ['code' => 'FIN-ACC', 'name' => 'Accounting'],
                ['code' => 'FIN-BGT', 'name' => 'Budget'],
            ],
            'HRA' => [
                ['code' => 'HRA-PCP', 'name' => 'People & Culture'],
                ['code' => 'HRA-GAD', 'name' => 'General Affairs'],
            ],
            'PRC' => [
                ['code' => 'PRC-PUR', 'name' => 'Purchasing'],
                ['code' => 'PRC-CTR', 'name' => 'Contract & Vendor Management'],
            ],
            'LGL' => [
                ['code' => 'LGL-COR', 'name' => 'Corporate Legal'],
                ['code' => 'LGL-CMP', 'name' => 'Compliance'],
            ],
        ];

        $unitMap = [];
        foreach ($functions as $f) {
            $parent = Unit::create(['code' => $f['code'], 'name' => $f['name'], 'type' => 'function']);
            $unitMap[$f['code']] = $parent;
            foreach ($departments[$f['code']] ?? [] as $d) {
                $child = Unit::create([
                    'parent_id' => $parent->id,
                    'code' => $d['code'],
                    'name' => $d['name'],
                    'type' => 'department',
                ]);
                $unitMap[$d['code']] = $child;
            }
        }

        $zones = ['ZN-A' => 'Zone A', 'ZN-B' => 'Zone B', 'ZN-C' => 'Zone C'];
        $sites = [
            'ZN-A' => ['ST-A1' => 'Plant A-1', 'ST-A2' => 'Terminal A-2'],
            'ZN-B' => ['ST-B1' => 'Plant B-1', 'ST-B2' => 'Depot B-2'],
            'ZN-C' => ['ST-C1' => 'Mine Site C-1', 'ST-C2' => 'Jetty C-2'],
        ];
        foreach ($zones as $code => $name) {
            $zone = Zone::create(['code' => $code, 'name' => $name]);
            foreach ($sites[$code] as $sCode => $sName) {
                Site::create(['zone_id' => $zone->id, 'code' => $sCode, 'name' => $sName]);
            }
        }

        $demoUsers = [
            ['name' => 'Admin CMP', 'email' => 'admin@cmp.local', 'role' => 'admin', 'unit' => 'LGL-CMP'],
            ['name' => 'Manager OPS', 'email' => 'manager@cmp.local', 'role' => 'manager', 'unit' => 'OPR-MNT'],
            ['name' => 'Editor HSSE', 'email' => 'editor@cmp.local', 'role' => 'editor', 'unit' => 'HSSE-SHE'],
            ['name' => 'Viewer Guest', 'email' => 'viewer@cmp.local', 'role' => 'viewer', 'unit' => 'ENG-TCH'],
        ];

        foreach ($demoUsers as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password'),
                'unit_id' => $unitMap[$u['unit']]->id,
                'job_title' => ucfirst($u['role']),
            ]);
            $user->roles()->attach(Role::where('code', $u['role'])->first()->id);
        }
    }
}