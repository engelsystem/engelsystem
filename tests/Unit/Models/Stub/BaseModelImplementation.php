<?php

namespace Engelsystem\Test\Unit\Models\Stub;

use Engelsystem\Models\BaseModel;

/**
 * @property string foo
 */
class BaseModelImplementation extends BaseModel
{
    /** @var array */
    protected $fillable = ['foo'];

    /** @var int */
    public $saveCount = 0;

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->saveCount++;
        return true;
    }
}
