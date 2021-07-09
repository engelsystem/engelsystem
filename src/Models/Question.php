<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int         $id
 * @property string      $text
 * @property string      $answer
 * @property int         $answerer_id
 * @property Carbon|null $answered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User   $answerer
 *
 * @method static Builder|Question whereAnswer($value)
 * @method static Builder|Question whereAnswererId($value)
 * @method static Builder|Question whereId($value)
 * @method static Builder|Question whereQuestion($value)
 */
class Question extends BaseModel
{
    use HasFactory;
    use UsesUserModel;

    /** @var bool Enable timestamps */
    public $timestamps = true;

    /** @var string[] */
    protected $dates = [
        'answered_at'
    ];

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'text',
        'answerer_id',
        'answer',
        'answered_at',
    ];

    /** @var string[] */
    protected $casts = [
        'user_id'     => 'integer',
        'answerer_id' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answerer_id');
    }

    /**
     * @return Builder
     */
    public static function unanswered(): Builder
    {
        return static::whereAnswererId(null);
    }

    /**
     * @return Builder|QueryBuilder
     */
    public static function answered(): Builder
    {
        return static::whereNotNull('answerer_id');
    }
}
