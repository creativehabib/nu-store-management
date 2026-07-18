<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredTokenHash = setting('api_token_hash');
        $providedToken = $request->bearerToken();

        if (! is_string($configuredTokenHash) || $configuredTokenHash === '' || ! is_string($providedToken) || $providedToken === '') {
            return $this->unauthorizedResponse();
        }

        if (! hash_equals($configuredTokenHash, hash('sha256', $providedToken))) {
            return $this->unauthorizedResponse();
        }

        return $next($request);
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Valid API bearer token is required.',
        ], 401);
    }
}
