<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int                                   $id
 * @property string                                $identifier
 * @property string                                $name
 * @property string|null                           $secret
 * @property array                                 $redirect_uris
 * @property string                                $grants
 * @property array|null                            $scopes
 * @property bool                                  $confidential
 * @property bool                                  $active
 * @property Carbon|null                           $created_at
 * @property Carbon|null                           $updated_at
 *
 * @property-read Collection<int, AngelType>       $angelTypes
 * @property-read Collection<int, OAuth2AccessToken> $accessTokens
 *
 * @method static QueryBuilder|OAuth2Client[] whereId($value)
 * @method static QueryBuilder|OAuth2Client[] whereIdentifier($value)
 * @method static QueryBuilder|OAuth2Client[] whereName($value)
 * @method static QueryBuilder|OAuth2Client[] whereActive($value)
 */
class OAuth2Client extends BaseModel
{
    use HasFactory;

    /** @var string */
    public $table = 'oauth2_clients'; // phpcs:ignore

    /** @var bool */
    public $timestamps = true; // phpcs:ignore

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'identifier',
        'name',
        'secret',
        'redirect_uris',
        'grants',
        'scopes',
        'confidential',
        'active',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'redirect_uris' => 'array',
        'scopes' => 'array',
        'confidential' => 'boolean',
        'active' => 'boolean',
    ];

    /** @var array<string> */
    protected $hidden = ['secret']; // phpcs:ignore

    public function angelTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            AngelType::class,
            'oauth2_client_angel_type',
            'oauth2_client_id',
            'angel_type_id'
        );
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(OAuth2AccessToken::class, 'oauth2_client_id');
    }

    /**
     * @return array<string>
     */
    public function getGrantsArray(): array
    {
        return array_map('trim', explode(',', $this->grants));
    }

    public function hasGrant(string $grant): bool
    {
        return in_array($grant, $this->getGrantsArray(), true);
    }

    public function isValidRedirectUri(string $uri): bool
    {
        return in_array($uri, $this->redirect_uris, true);
    }
}
