<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $map_url
 * @property string      $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static QueryBuilder|Room[] whereId($value)
 * @method static QueryBuilder|Room[] whereName($value)
 * @method static QueryBuilder|Room[] whereMapUrl($value)
 * @method static QueryBuilder|Room[] whereDescription($value)
 * @method static QueryBuilder|Room[] whereCreatedAt($value)
 * @method static QueryBuilder|Room[] whereUpdatedAt($value)
 */
class Room extends BaseModel
{
    use HasFactory;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var array */
    protected $fillable = [
        'name',
        'map_url',
        'description',
    ];
}
