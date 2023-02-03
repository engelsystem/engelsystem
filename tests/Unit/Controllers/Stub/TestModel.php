<?php

declare(strict_types=1);

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
    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'text',
        'another_text',
    ];
}
