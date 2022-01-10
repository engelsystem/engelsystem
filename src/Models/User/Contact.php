<?php

namespace Engelsystem\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property string|null $dect
 * @property string|null $email
 * @property string|null $mobile
 *
 * @method static QueryBuilder|Contact[] whereDect($value)
 * @method static QueryBuilder|Contact[] whereEmail($value)
 * @method static QueryBuilder|Contact[] whereMobile($value)
 */
class Contact extends HasUserModel
{
    use HasFactory;

    /** @var string The table associated with the model */
    protected $table = 'users_contact';

    /** The attributes that are mass assignable */
    protected $fillable = [
        'user_id',
        'dect',
        'email',
        'mobile',
    ];
}
