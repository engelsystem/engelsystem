<?php

declare(strict_types=1);

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string $language
 * @property int    $theme
 * @property bool   $email_human
 * @property bool   $email_messages
 * @property bool   $email_goodie
 * @property bool   $email_shiftinfo
 * @property bool   $email_news
 * @property bool   $mobile_show
 *
 * @method static QueryBuilder|Settings[] whereLanguage($value)
 * @method static QueryBuilder|Settings[] whereTheme($value)
 * @method static QueryBuilder|Settings[] whereEmailHuman($value)
 * @method static QueryBuilder|Settings[] whereEmailMessages($value)
 * @method static QueryBuilder|Settings[] whereEmailGoodie($value)
 * @method static QueryBuilder|Settings[] whereEmailShiftinfo($value)
 * @method static QueryBuilder|Settings[] whereEmailNews($value)
 * @method static QueryBuilder|Settings[] whereMobileShow($value)
 */
class Settings extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_settings'; // phpcs:ignore

    /** @var array<string, bool> Default attributes */
    protected $attributes = [ // phpcs:ignore
        'email_human'     => false,
        'email_messages'  => false,
        'email_goodie'    => false,
        'email_shiftinfo' => false,
        'email_news'      => false,
        'mobile_show'     => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [ // phpcs:ignore
        'user_id',
        'language',
        'theme',
        'email_human',
        'email_messages',
        'email_goodie',
        'email_shiftinfo',
        'email_news',
        'mobile_show',
    ];

    /** @var string[] */
    protected $casts = [ // phpcs:ignore
        'user_id'         => 'integer',
        'theme'           => 'integer',
        'email_human'     => 'boolean',
        'email_messages'  => 'boolean',
        'email_goodie'    => 'boolean',
        'email_shiftinfo' => 'boolean',
        'email_news'      => 'boolean',
        'mobile_show'     => 'boolean',
    ];
}
