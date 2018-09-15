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
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->saveCount++;
        return true;
    }

    /**
     * @return QueryBuilder
     */
    public static function query()
    {
        return self::$queryBuilder;
    }
}
