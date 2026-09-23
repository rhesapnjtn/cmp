<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HsseController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\OgbController;
use App\Http\Controllers\Api\PlanningController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RiskController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/filters', [AuthController::class, 'filters']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:reports.view');
    Route::get('/reports/export', [ReportController::class, 'export'])->middleware('permission:reports.view');

    // ---- Master data ----
    Route::get('/master/units', [MasterController::class, 'indexUnits']);
    Route::get('/master/zones', [MasterController::class, 'indexZones']);
    Route::get('/master/sites', [MasterController::class, 'indexSites']);

    Route::middleware('permission:masterdata.manage')->group(function () {
        Route::post('/master/units', [MasterController::class, 'storeUnit']);
        Route::put('/master/units/{unit}', [MasterController::class, 'updateUnit']);
        Route::delete('/master/units/{unit}', [MasterController::class, 'destroyUnit']);
        Route::post('/master/zones', [MasterController::class, 'storeZone']);
        Route::put('/master/zones/{zone}', [MasterController::class, 'updateZone']);
        Route::delete('/master/zones/{zone}', [MasterController::class, 'destroyZone']);
        Route::post('/master/sites', [MasterController::class, 'storeSite']);
        Route::put('/master/sites/{site}', [MasterController::class, 'updateSite']);
        Route::delete('/master/sites/{site}', [MasterController::class, 'destroySite']);
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/master/users', [MasterController::class, 'indexUsers']);
        Route::post('/master/users', [MasterController::class, 'storeUser']);
        Route::put('/master/users/{user}', [MasterController::class, 'updateUser']);
        Route::delete('/master/users/{user}', [MasterController::class, 'destroyUser']);
        Route::get('/master/roles', [MasterController::class, 'indexRoles']);
    });

    // ---- Planning ----
    Route::middleware('permission:planning.view')->group(function () {
        Route::get('/planning', [PlanningController::class, 'index']);
        Route::get('/planning/{plan}', [PlanningController::class, 'show']);
        Route::get('/planning/{plan}/tasks', [PlanningController::class, 'indexTasks']);
    });
    Route::middleware('permission:planning.manage')->group(function () {
        Route::post('/planning', [PlanningController::class, 'store']);
        Route::put('/planning/{plan}', [PlanningController::class, 'update']);
        Route::delete('/planning/{plan}', [PlanningController::class, 'destroy']);
        Route::post('/planning/{plan}/tasks', [PlanningController::class, 'storeTask']);
        Route::put('/planning/{plan}/tasks/{task}', [PlanningController::class, 'updateTask']);
        Route::delete('/planning/{plan}/tasks/{task}', [PlanningController::class, 'destroyTask']);
    });

    // ---- Risk ----
    Route::middleware('permission:risk.view')->group(function () {
        Route::get('/risk', [RiskController::class, 'index']);
        Route::get('/risk/{risk}', [RiskController::class, 'show']);
    });
    Route::middleware('permission:risk.manage')->group(function () {
        Route::post('/risk', [RiskController::class, 'store']);
        Route::put('/risk/{risk}', [RiskController::class, 'update']);
        Route::delete('/risk/{risk}', [RiskController::class, 'destroy']);
    });

    // ---- OGB ----
    Route::middleware('permission:ogb.view')->group(function () {
        Route::get('/ogb', [OgbController::class, 'index']);
        Route::get('/ogb/{contract}', [OgbController::class, 'show']);
        Route::get('/ogb/{contract}/milestones', [OgbController::class, 'indexMilestones']);
    });
    Route::middleware('permission:ogb.manage')->group(function () {
        Route::post('/ogb', [OgbController::class, 'store']);
        Route::put('/ogb/{contract}', [OgbController::class, 'update']);
        Route::delete('/ogb/{contract}', [OgbController::class, 'destroy']);
        Route::post('/ogb/{contract}/milestones', [OgbController::class, 'storeMilestone']);
        Route::put('/ogb/{contract}/milestones/{milestone}', [OgbController::class, 'updateMilestone']);
        Route::delete('/ogb/{contract}/milestones/{milestone}', [OgbController::class, 'destroyMilestone']);
    });

    // ---- HSSE ----
    Route::middleware('permission:hsse.view')->group(function () {
        Route::get('/hsse', [HsseController::class, 'index']);
        Route::get('/hsse/{meeting}', [HsseController::class, 'show']);
    });
    Route::middleware('permission:hsse.manage')->group(function () {
        Route::post('/hsse', [HsseController::class, 'store']);
        Route::put('/hsse/{meeting}', [HsseController::class, 'update']);
        Route::delete('/hsse/{meeting}', [HsseController::class, 'destroy']);
    });

    // ---- Compliance ----
    Route::middleware('permission:compliance.view')->group(function () {
        Route::get('/compliance', [ComplianceController::class, 'index']);
        Route::get('/compliance/{item}', [ComplianceController::class, 'show']);
    });
    Route::middleware('permission:compliance.manage')->group(function () {
        Route::post('/compliance', [ComplianceController::class, 'store']);
        Route::put('/compliance/{item}', [ComplianceController::class, 'update']);
        Route::delete('/compliance/{item}', [ComplianceController::class, 'destroy']);
        Route::post('/compliance/{item}/evidence', [ComplianceController::class, 'storeEvidence']);
        Route::delete('/compliance/{item}/evidence/{evidence}', [ComplianceController::class, 'destroyEvidence']);
    });
});