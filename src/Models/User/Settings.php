<?php

namespace Engelsystem\Models\User;

/**
 * @property string $language
 * @property int    $theme
 * @property bool   $email_human
 * @property bool   $email_shiftinfo
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Settings[] whereLanguage($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Settings[] whereTheme($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Settings[] whereEmailHuman($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Settings[] whereEmailShiftinfo($value)
 */
class Settings extends HasUserModel
{
    /** @var string The table associated with the model */
    protected $table = 'users_settings';

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'email_human',
        'email_shiftinfo',
    ];
}
