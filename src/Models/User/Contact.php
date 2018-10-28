<?php

namespace Engelsystem\Models\User;

/**
 * @property string|null $dect
 * @property string|null $email
 * @property string|null $mobile
 *
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereDect($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\Engelsystem\Models\User\Contact[] whereMobile($value)
 */
class Contact extends HasUserModel
{
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
