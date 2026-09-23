<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MasterController extends Controller
{
    public function indexUnits(Request $request)
    {
        $units = Unit::with('parent')->withCount('children')->orderBy('type')->orderBy('name')->get();

        return response()->json(['data' => $units]);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:units,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:function,department',
            'parent_id' => 'nullable|exists:units,id',
        ]);

        return response()->json(['data' => Unit::create($data)], 201);
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:units,code,'.$unit->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:function,department',
            'parent_id' => 'nullable|exists:units,id',
        ]);

        $unit->update($data);

        return response()->json(['data' => $unit->load('parent')]);
    }

    public function destroyUnit(Unit $unit)
    {
        if ($unit->children()->exists()) {
            return response()->json(['message' => 'Cannot delete: unit has children.'], 422);
        }
        $unit->delete();

        return response()->json(['message' => 'Unit deleted.']);
    }

    public function indexZones(Request $request)
    {
        return response()->json(['data' => Zone::with('sites')->orderBy('name')->get()]);
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:zones,code',
            'name' => 'required|string|max:255',
        ]);

        return response()->json(['data' => Zone::create($data)], 201);
    }

    public function updateZone(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:zones,code,'.$zone->id,
            'name' => 'required|string|max:255',
        ]);

        $zone->update($data);

        return response()->json(['data' => $zone]);
    }

    public function destroyZone(Zone $zone)
    {
        if ($zone->sites()->exists()) {
            return response()->json(['message' => 'Cannot delete: zone has sites.'], 422);
        }
        $zone->delete();

        return response()->json(['message' => 'Zone deleted.']);
    }

    public function indexSites(Request $request)
    {
        return response()->json(['data' => Site::with('zone:id,name')->orderBy('name')->get()]);
    }

    public function storeSite(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:sites,code',
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);

        return response()->json(['data' => Site::create($data)->load('zone:id,name')], 201);
    }

    public function updateSite(Request $request, Site $site)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:sites,code,'.$site->id,
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);

        $site->update($data);

        return response()->json(['data' => $site->load('zone:id,name')]);
    }

    public function destroySite(Site $site)
    {
        $site->delete();

        return response()->json(['message' => 'Site deleted.']);
    }

    public function indexUsers(Request $request)
    {
        $users = User::with(['unit', 'roles:id,name,code'])
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'job_title' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'nullable|boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'job_title' => $data['job_title'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        $user->roles()->attach($data['roles']);

        return response()->json(['data' => $user->load(['unit', 'roles'])], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'job_title' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'nullable|boolean',
            'roles' => 'nullable|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'job_title' => $data['job_title'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }
        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return response()->json(['data' => $user->load(['unit', 'roles'])]);
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }
        $user->update(['is_active' => false]);

        return response()->json(['message' => 'User deactivated.']);
    }

    public function indexRoles()
    {
        return response()->json(['data' => Role::with('permissions')->orderBy('name')->get()]);
    }
}