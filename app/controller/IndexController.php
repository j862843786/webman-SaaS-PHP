<?php

namespace app\controller;

use app\tenant\TenantContext;
use Webman\Http\Request;
use Webman\Http\Response;

final class IndexController
{
    public function index(Request $request): Response
    {
        return json([
            'name' => 'webman SaaS foundation',
            'tenant' => TenantContext::get(),
            'extensions' => [
                'applications' => config('app_loader.applications', []),
                'plugins' => config('plugin_loader.plugins', []),
            ],
        ]);
    }

    public function health(Request $request): Response
    {
        return json([
            'status' => 'ok',
            'tenant_code' => TenantContext::code(),
        ]);
    }
}
