<?php

namespace app\support;

use Webman\Http\Response;

final class ApiResponse
{
    public static function success(array $data = [], string $message = 'ok'): Response
    {
        return json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message, int $code = 400, array $data = []): Response
    {
        $response = json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);

        return $response->withStatus($code >= 100 && $code < 600 ? $code : 400);
    }
}
