<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\Stub;

use Engelsystem\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

/**
 * @property string foo
 */
class BaseModelImplementation extends BaseModel
{
    /** @var array<string> */
    protected $fillable = ['foo']; // phpcs:ignore

    public int $saveCount = 0;

    public static ?QueryBuilder $queryBuilder = null;

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
