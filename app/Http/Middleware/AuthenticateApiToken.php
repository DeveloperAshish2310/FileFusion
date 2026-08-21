<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request with Bearer API Token.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$abilities
     */
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        $rawToken = $this->extractToken($request);

        if (empty($rawToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Missing API Bearer token.',
            ], 401);
        }

        // Expected format: ff_live_<token_id>.<plain_secret>
        if (!preg_match('/^ff_live_(ff_tok_[a-z0-9]+)\.([A-Za-z0-9]+)$/', $rawToken, $matches)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token format. Expected ff_live_<id>.<secret>',
            ], 401);
        }

        $tokenId = $matches[1];
        $plainSecret = $matches[2];

        $token = ApiToken::where('token_id', $tokenId)->with('user')->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or revoked API token.',
            ], 401);
        }

        // Constant-time hash verification
        $computedHash = hash('sha256', $plainSecret);
        if (!hash_equals($token->token_hash, $computedHash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token credentials.',
            ], 401);
        }

        // Expiration check
        if ($token->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'API token has expired.',
            ], 401);
        }

        // User active status check
        $user = $token->user;
        if (!$user || !$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'The user account associated with this token is inactive or deleted.',
            ], 403);
        }

        // Check if user is approved to use API
        if (!$user->canUseApi()) {
            return response()->json([
                'success' => false,
                'message' => 'API access is disabled for your account. Please contact an administrator.',
            ], 403);
        }

        // Check required abilities if specified in route
        if (!empty($abilities)) {
            foreach ($abilities as $ability) {
                if ($token->cant($ability)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Forbidden. Token lacks required ability: {$ability}",
                        'required_abilities' => $abilities,
                        'token_abilities' => $token->abilities,
                    ], 403);
                }
            }
        }

        // Update token telemetry
        $token->timestamps = false;
        $token->last_used_at = now();
        $token->last_used_ip = $request->ip();
        $token->saveQuietly();

        // Authenticate user for the request lifecycle
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('api_token', $token);

        try {
            Auth::setUser($user);
        } catch (\Throwable $e) {
            // Stateless fallback
        }

        return $next($request);
    }

    /**
     * Extract token string from Bearer header, X-API-TOKEN header, or query string.
     */
    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/^Bearer\s+(.*)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        if ($request->hasHeader('X-API-TOKEN')) {
            return trim($request->header('X-API-TOKEN'));
        }

        if ($request->filled('api_token')) {
            return trim($request->query('api_token'));
        }

        return null;
    }
}
