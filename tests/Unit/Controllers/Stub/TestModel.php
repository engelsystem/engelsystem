<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $text
 * @property string      $another_text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TestModel extends Model
{
    /** @var string[] */
    protected $fillable = [
        'text',
        'another_text',
    ];
}
