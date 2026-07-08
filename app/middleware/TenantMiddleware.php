<?php

namespace app\middleware;

use app\tenant\TenantContext;
use app\tenant\TenantResolver;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

final class TenantMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $tenant = (new TenantResolver())->resolve($request);
        TenantContext::set($tenant);

        try {
            return $handler($request);
        } finally {
            TenantContext::clear();
        }
    }
}
