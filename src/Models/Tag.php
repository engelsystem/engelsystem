<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 *
 * @property-read Collection|Faq[]   $faqs
 * @property-read Collection|Shift[] $shifts
 *
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 */
class Tag extends BaseModel
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [ // phpcs:ignore
        'name',
    ];

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'faq_tags');
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_tags');
    }
}
