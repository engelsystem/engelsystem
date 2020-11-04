<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use ArrayAccess;
use Engelsystem\Controllers\CleanupModel;
use Illuminate\Database\Eloquent\Model;

class CleanupModelImplementation
{
    use CleanupModel;

    /**
     * @param Model|ArrayAccess|Model[] $model
     * @param array                     $attributes
     */
    public function cleanup($model, array $attributes = []): void
    {
        $this->cleanupModelNullValues($model, $attributes);
    }
}
