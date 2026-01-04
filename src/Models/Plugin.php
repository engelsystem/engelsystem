<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property string $version
 *
 * @method static Builder|Plugin[] whereId($value)
 * @method static Builder|Plugin[] whereName($value)
 * @method static Builder|Plugin[] whereEnabled($value)
 * @method static Builder|Plugin[] whereVersion($value)
 */
class Plugin extends BaseModel
{
    use HasFactory;

    /** @var array<string, null> default attributes */
    protected $attributes = [ // phpcs:ignore
        'enabled' => false,
        'version' => '0.0.0',
    ];

    /** @var array<string, string> */
    protected $casts = [ // phpcs:ignore
        'enabled' => 'boolean',
    ];

    /** @var string[] */
    protected $fillable = [ // phpcs:ignore
        'name',
        'enabled',
        'version',
    ];
}
