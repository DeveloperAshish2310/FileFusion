<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'token_id',
        'token_hash',
        'abilities',
        'last_used_at',
        'last_used_ip',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * User relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new API token pair (Identifier + Plain Secret).
     *
     * @return array{plainToken: string, token: ApiToken}
     */
    public static function generate(
        User $user,
        string $name,
        array $abilities = ['*'],
        ?Carbon $expiresAt = null
    ): array {
        $tokenId = 'ff_tok_' . Str::lower(Str::random(10));
        $plainSecret = Str::random(40);
        $tokenHash = hash('sha256', $plainSecret);

        $token = self::create([
            'user_id' => $user->id,
            'name' => trim($name),
            'token_id' => $tokenId,
            'token_hash' => $tokenHash,
            'abilities' => empty($abilities) ? ['*'] : array_values(array_unique($abilities)),
            'expires_at' => $expiresAt,
        ]);

        $plainToken = "ff_live_{$tokenId}.{$plainSecret}";

        return [
            'plainToken' => $plainToken,
            'token' => $token,
        ];
    }

    /**
     * Check if the token has the given ability scope.
     */
    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        // Wildcard super-scope
        if (in_array('*', $abilities, true)) {
            return true;
        }

        if (in_array($ability, $abilities, true)) {
            return true;
        }

        // Section wildcard e.g. "files:*" matches "files:read"
        if (str_contains($ability, ':')) {
            [$section, $action] = explode(':', $ability, 2);
            if (in_array("{$section}:*", $abilities, true)) {
                return true;
            }

            // Categories fallback: allow if token has links/files ability
            if ($section === 'categories') {
                if (in_array("links:{$action}", $abilities, true) || in_array("files:{$action}", $abilities, true)
                    || in_array('links:*', $abilities, true) || in_array('files:*', $abilities, true)) {
                    return true;
                }
            }

            // To-Dos fallback: allow if token has links/files ability
            if ($section === 'todos') {
                if (in_array("links:{$action}", $abilities, true) || in_array("files:{$action}", $abilities, true)
                    || in_array('links:*', $abilities, true) || in_array('files:*', $abilities, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Inverse of can().
     */
    public function cant(string $ability): bool
    {
        return !$this->can($ability);
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
