<?php

namespace app\admin\controller;

use app\service\TenantService;
use app\support\ApiResponse;
use InvalidArgumentException;
use Webman\Http\Request;
use Webman\Http\Response;

final class TenantController
{
    public function index(Request $request): Response
    {
        return ApiResponse::success(['items' => $this->service()->list()]);
    }

    public function show(Request $request, int $id): Response
    {
        $tenant = $this->service()->find($id);

        return $tenant ? ApiResponse::success($tenant) : ApiResponse::error('tenant not found', 404);
    }

    public function store(Request $request): Response
    {
        try {
            $id = $this->service()->create($request->all());
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(['id' => $id], 'created');
    }

    public function update(Request $request, int $id): Response
    {
        $this->service()->update($id, $request->all());

        return ApiResponse::success(message: 'updated');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->service()->delete($id);

        return ApiResponse::success(message: 'deleted');
    }

    public function domains(Request $request, int $id): Response
    {
        return ApiResponse::success(['items' => $this->service()->domains($id)]);
    }

    public function addDomain(Request $request, int $id): Response
    {
        try {
            $domainId = $this->service()->addDomain($id, (string) $request->post('domain', ''));
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(['id' => $domainId], 'created');
    }

    public function removeDomain(Request $request, int $id, int $domainId): Response
    {
        $this->service()->removeDomain($id, $domainId);

        return ApiResponse::success(message: 'deleted');
    }

    private function service(): TenantService
    {
        return new TenantService();
    }
}
