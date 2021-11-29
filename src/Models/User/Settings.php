<?php

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string $language
 * @property int    $theme
 * @property bool   $email_human
 * @property bool   $email_goody
 * @property bool   $email_shiftinfo
 * @property bool   $email_news
 *
 * @method static QueryBuilder|Settings[] whereLanguage($value)
 * @method static QueryBuilder|Settings[] whereTheme($value)
 * @method static QueryBuilder|Settings[] whereEmailHuman($value)
 * @method static QueryBuilder|Settings[] whereEmailGoody($value)
 * @method static QueryBuilder|Settings[] whereEmailShiftinfo($value)
 * @method static QueryBuilder|Settings[] whereEmailNews($value)
 */
class Settings extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_settings';

    /** @var array Default attributes */
    protected $attributes = [
        'email_human'     => false,
        'email_goody'     => false,
        'email_shiftinfo' => false,
        'email_news'      => false,
    ];

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'email_human',
        'email_goody',
        'email_shiftinfo',
        'email_news',
    ];

    /** @var string[] */
    protected $casts = [
        'user_id'         => 'integer',
        'theme'           => 'integer',
        'email_human'     => 'boolean',
        'email_goody'     => 'boolean',
        'email_shiftinfo' => 'boolean',
        'email_news'      => 'boolean',
    ];
}
