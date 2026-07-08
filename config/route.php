<?php

use Webman\Route;

Route::get('/', [app\controller\IndexController::class, 'index']);
Route::get('/health', [app\controller\IndexController::class, 'health']);

Route::group('/admin', static function (): void {
    Route::get('/tenants', [app\admin\controller\TenantController::class, 'index']);
    Route::post('/tenants', [app\admin\controller\TenantController::class, 'store']);
    Route::get('/tenants/{id:\d+}', [app\admin\controller\TenantController::class, 'show']);
    Route::put('/tenants/{id:\d+}', [app\admin\controller\TenantController::class, 'update']);
    Route::delete('/tenants/{id:\d+}', [app\admin\controller\TenantController::class, 'destroy']);
    Route::get('/tenants/{id:\d+}/domains', [app\admin\controller\TenantController::class, 'domains']);
    Route::post('/tenants/{id:\d+}/domains', [app\admin\controller\TenantController::class, 'addDomain']);
    Route::delete('/tenants/{id:\d+}/domains/{domainId:\d+}', [app\admin\controller\TenantController::class, 'removeDomain']);

    Route::get('/applications', [app\admin\controller\ExtensionController::class, 'applications']);
    Route::post('/applications', [app\admin\controller\ExtensionController::class, 'saveApplication']);
    Route::post('/tenants/{tenantId:\d+}/applications/{code}', [app\admin\controller\ExtensionController::class, 'enableTenantApplication']);

    Route::get('/plugins', [app\admin\controller\ExtensionController::class, 'plugins']);
    Route::post('/plugins', [app\admin\controller\ExtensionController::class, 'savePlugin']);
    Route::post('/tenants/{tenantId:\d+}/plugins/{code}', [app\admin\controller\ExtensionController::class, 'enableTenantPlugin']);
});
