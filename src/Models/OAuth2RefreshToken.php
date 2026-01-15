<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string           $id
 * @property string           $access_token_id
 * @property bool             $revoked
 * @property Carbon           $expires_at
 * @property Carbon|null      $created_at
 * @property Carbon|null      $updated_at
 *
 * @property-read OAuth2AccessToken $accessToken
 *
 * @method static QueryBuilder|OAuth2RefreshToken[] whereId($value)
 * @method static QueryBuilder|OAuth2RefreshToken[] whereAccessTokenId($value)
 * @method static QueryBuilder|OAuth2RefreshToken[] whereRevoked($value)
 */
class OAuth2RefreshToken extends BaseModel
{
    use HasFactory;

    /** @var string */
    public $table = 'oauth2_refresh_tokens'; // phpcs:ignore

    /** @var bool */
    public $timestamps = true; // phpcs:ignore

    /** @var bool */
    public $incrementing = false; // phpcs:ignore

    /** @var string */
    protected $keyType = 'string'; // phpcs:ignore

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'id',
        'access_token_id',
        'revoked',
        'expires_at',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(OAuth2AccessToken::class, 'access_token_id');
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
    }
}
