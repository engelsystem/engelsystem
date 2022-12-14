<?php

namespace Engelsystem\Test\Unit\Models\Stub;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

/**
 * @property string foo
 */
class BaseModelImplementation extends BaseModel
{
    /** @var array */
    protected $fillable = ['foo'];

    /** @var int */
    public $saveCount = 0;

    /** @var QueryBuilder */
    public static $queryBuilder = null;

    /**
     * @param array $options
     */
    public function save(array $options = []): bool
    {
        $this->saveCount++;
        return true;
    }

    public static function query(): QueryBuilder
    {
        return self::$queryBuilder;
    }
}
