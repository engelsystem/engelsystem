<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int         $id
 * @property string      $question
 * @property string      $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
}
