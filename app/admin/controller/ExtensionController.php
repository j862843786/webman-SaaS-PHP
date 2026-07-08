<?php

namespace app\admin\controller;

use app\service\ExtensionService;
use app\support\ApiResponse;
use InvalidArgumentException;
use Webman\Http\Request;
use Webman\Http\Response;

final class ExtensionController
{
    public function applications(Request $request): Response
    {
        return ApiResponse::success(['items' => $this->service()->applications()]);
    }

    public function saveApplication(Request $request): Response
    {
        try {
            $this->service()->saveApplication($request->all());
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(message: 'saved');
    }

    public function plugins(Request $request): Response
    {
        return ApiResponse::success(['items' => $this->service()->plugins()]);
    }

    public function savePlugin(Request $request): Response
    {
        try {
            $this->service()->savePlugin($request->all());
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(message: 'saved');
    }

    public function enableTenantApplication(Request $request, int $tenantId, string $code): Response
    {
        $this->service()->enableTenantApplication($tenantId, $code, (array) $request->post('config', []));

        return ApiResponse::success(message: 'enabled');
    }

    public function enableTenantPlugin(Request $request, int $tenantId, string $code): Response
    {
        $this->service()->enableTenantPlugin($tenantId, $code, (array) $request->post('config', []));

        return ApiResponse::success(message: 'enabled');
    }

    private function service(): ExtensionService
    {
        return new ExtensionService();
    }
}
