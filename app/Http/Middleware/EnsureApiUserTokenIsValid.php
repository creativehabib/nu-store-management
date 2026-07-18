<?php

namespace App\Http\Middleware;

use App\Models\ApiUserToken;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedToken = $request->bearerToken();

        if (! is_string($providedToken) || $providedToken === '') {
            return $this->unauthorizedResponse();
        }

        $apiUserToken = ApiUserToken::query()
            ->with('user.department', 'user.designation')
            ->where('token_hash', hash('sha256', $providedToken))
            ->first();

        if (! $apiUserToken) {
            return $this->unauthorizedResponse();
        }

        $apiUserToken->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $apiUserToken->user);
        $request->attributes->set('api_user_token', $apiUserToken);

        return $next($request);
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Valid user bearer token is required.',
        ], 401);
    }
}
