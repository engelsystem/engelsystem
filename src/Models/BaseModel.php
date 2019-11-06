<?php

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
abstract class BaseModel extends Model
{
    /** @var bool Disable timestamps by default because of "Datensparsamkeit" */
    public $timestamps = false;
}
