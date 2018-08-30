<?php

namespace Engelsystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LogEntry extends BaseModel
{
    /** @var bool enable timestamps for created_at */
    public $timestamps = true;

    /** @var null Disable updated_at */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'level',
        'message',
    ];

    /**
     * @param $keyword
     * @return Builder[]|Collection|LogEntry[]
     */
    public static function filter($keyword = null)
    {
        $query = LogEntry::query()
            ->select()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10000);

        if (!empty($keyword)) {
            $query
                ->where('level', '=', $keyword)
                ->orWhere('message', 'LIKE', '%' . $keyword . '%');
        }

        return $query->get();
    }
}
