<?php

namespace app\middleware;

use app\support\ApiResponse;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

final class AdminTokenMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $token = (string) config('admin.api_token', '');
        if ($token === '') {
            return $handler($request);
        }

        $provided = (string) $request->header('X-Admin-Token', '');
        if (!hash_equals($token, $provided)) {
            return ApiResponse::error('unauthorized', 401);
        }

        return $handler($request);
    }
}
