<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string                                  $id
 * @property int                                     $oauth2_client_id
 * @property int|null                                $user_id
 * @property array|null                              $scopes
 * @property bool                                    $revoked
 * @property Carbon                                  $expires_at
 * @property Carbon|null                             $created_at
 * @property Carbon|null                             $updated_at
 *
 * @property-read OAuth2Client                       $client
 * @property-read User|null                          $user
 * @property-read Collection<int, OAuth2RefreshToken> $refreshTokens
 *
 * @method static QueryBuilder|OAuth2AccessToken[] whereId($value)
 * @method static QueryBuilder|OAuth2AccessToken[] whereUserId($value)
 * @method static QueryBuilder|OAuth2AccessToken[] whereRevoked($value)
 */
class OAuth2AccessToken extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var string */
    public $table = 'oauth2_access_tokens'; // phpcs:ignore

    /** @var bool */
    public $timestamps = true; // phpcs:ignore

    /** @var bool */
    public $incrementing = false; // phpcs:ignore

    /** @var string */
    protected $keyType = 'string'; // phpcs:ignore

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'id',
        'oauth2_client_id',
        'user_id',
        'scopes',
        'revoked',
        'expires_at',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'oauth2_client_id' => 'integer',
        'user_id' => 'integer',
        'scopes' => 'array',
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(OAuth2Client::class, 'oauth2_client_id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(OAuth2RefreshToken::class, 'access_token_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->revoked && !$this->isExpired();
    }

    public function revoke(): void
    {
        $this->revoked = true;
        $this->save();

        $this->refreshTokens()->update(['revoked' => true]);
    }
}
