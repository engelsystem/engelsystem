<?php

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 *
 * @method static QueryBuilder newModelQuery()
 * @method static QueryBuilder newQuery()
 * @method static QueryBuilder query()
 */
abstract class BaseModel extends Model
{
    /** @var bool Disable timestamps by default because of "Datensparsamkeit" */
    public $timestamps = false;
}
