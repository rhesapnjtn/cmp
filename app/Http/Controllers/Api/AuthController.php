<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with(['unit', 'roles'])->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated.'],
            ]);
        }

        $token = $user->createToken('cmp-api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['unit', 'roles']);

        return response()->json(['user' => $this->userPayload($user)]);
    }

    public function filters(Request $request)
    {
        $years = \App\Models\Plan::query()
            ->selectRaw('year')
            ->union(\App\Models\RiskRegister::query()->select('year'))
            ->union(\App\Models\Contract::query()->select('year'))
            ->union(\App\Models\HsseMeeting::query()->select('year'))
            ->union(\App\Models\ComplianceItem::query()->select('year'))
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return response()->json([
            'years' => $years->map(fn ($y) => (int) $y)->values(),
            'quarters' => [1, 2, 3, 4],
            'units' => $this->unitTree(),
            'zones' => \App\Models\Zone::with('sites')->orderBy('name')->get(),
            'sites' => \App\Models\Site::with('zone:id,name')->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'job_title', 'email']),
            'levels' => [1, 2, 3],
        ]);
    }

    protected function unitTree()
    {
        return \App\Models\Unit::with(['children' => function ($q) {
            $q->orderBy('name');
        }])->whereNull('parent_id')->orderBy('name')->get();
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'job_title' => $user->job_title,
            'is_active' => $user->is_active,
            'unit' => $user->unit,
            'roles' => $user->roles,
            'permissions' => $user->allPermissions(),
        ];
    }
}