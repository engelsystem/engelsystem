<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string      $language_code
 * @property bool        $is_native
 *
 * @property-read User   $user
 *
 * @method static QueryBuilder|UserLanguage[] whereId($value)
 * @method static QueryBuilder|UserLanguage[] whereUserId($value)
 * @method static QueryBuilder|UserLanguage[] whereLanguageCode($value)
 * @method static QueryBuilder|UserLanguage[] whereIsNative($value)
 */
class UserLanguage extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var string The table associated with the model */
    protected $table = 'user_languages'; // phpcs:ignore

    /** @var array<string, bool> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'is_native' => false,
    ];

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'language_code',
        'is_native',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'user_id' => 'integer',
        'is_native' => 'boolean',
    ];
}
