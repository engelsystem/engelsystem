<?php

declare(strict_types=1);

namespace Engelsystem\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UsesUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer   $id
 * @property string    $text
 * @property string    $answer
 * @property integer   $answerer_id
 * @property-read User $answerer
 * @method static Builder|Question whereAnswer($value)
 * @method static Builder|Question whereAnswererId($value)
 * @method static Builder|Question whereId($value)
 * @method static Builder|Question whereQuestion($value)
 */
class Question extends BaseModel
{
    use UsesUserModel;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'text',
        'answerer_id',
        'answer',
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
     * @return Builder
     */
    public static function answered(): Builder
    {
        return static::whereNotNull('answerer_id');
    }
}
