<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int         $id
 * @property string      $question
 * @property string      $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection|Tag[] $tags
 *
 * @method static Builder|Faq whereId($value)
 * @method static Builder|Faq whereQuestion($value)
 * @method static Builder|Faq whereText($value)
 */
class Faq extends BaseModel
{
    use HasFactory;

    /** @var bool Enable timestamps */
    public $timestamps = true; // phpcs:ignore

    /** @var string The models table */
    public $table = 'faq'; // phpcs:ignore

    /** @var array<string> */
    protected $fillable = [ // phpcs:ignore
        'question',
        'text',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'faq_tags');
    }
}
