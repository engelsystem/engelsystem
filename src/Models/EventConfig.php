<?php

namespace Engelsystem\Models;

use Carbon\Carbon;

class EventConfig extends BaseModel
{
    /** @var string The primary key for the model */
    protected $primaryKey = 'name';

    /** @var bool Indicates if the IDs are auto-incrementing */
    public $incrementing = false;

    /** @var string Required because it is not event_configs */
    protected $table = 'event_config';

    /** @var array Values that are mass assignable */
    protected $fillable = ['name', 'value'];

    /** @var array The configuration values that should be cast to native types */
    protected $valueCasts = [
        'buildup_start' => 'date',
        'event_start'   => 'date',
        'event_end'     => 'date',
        'teardown_end'  => 'date',
    ];

    /** @var bool It could be interesting to know when a value changed the last time */
    public $timestamps = true;

    /**
     * Value accessor
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        $value = $this->fromJson($value);

        /** @see \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute */
        if (!empty($value)) {
            switch ($this->getValueCast($this->name)) {
                case 'date':
                    return Carbon::createFromFormat('Y-m-d', $value)
                        ->setTime(0, 0);
                case 'datetime':
                    return Carbon::createFromFormat(Carbon::ISO8601, $value);
            }
        }

        return $value;
    }

    /**
     * Value mutator
     *
     * @param mixed $value
     * @return static
     */
    public function setValueAttribute($value)
    {
        if (!empty($value)) {
            switch ($this->getValueCast($this->name)) {
                case 'date':
                    /** @var Carbon $value */
                    $value = $value->toDateString();
                    break;
                case 'datetime':
                    /** @var Carbon $value */
                    $value = $value->toIso8601String();
                    break;
            }
        }

        $value = $this->castAttributeAsJson('value', $value);
        $this->attributes['value'] = $value;

        return $this;
    }

    /**
     * Check if the value has to be casted
     *
     * @param string $value
     * @return string|null
     */
    protected function getValueCast($value)
    {
        return isset($this->valueCasts[$value]) ? $this->valueCasts[$value] : null;
    }
}
