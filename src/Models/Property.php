<?php

namespace Properties\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /**
    * The primary key column name.
    *
    * @var array
    */
    public $primaryKey = 'key';

    /**
    * Whether the primary key is incremental.
    *
    * @var boolean
    */
    public $increments = false;

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'key'     => 'string',
        'type'    => 'string',
        'targets' => 'json',
    ];

    /**
    * The attributes that aren't mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'key',
        'type',
        'targets',
        'default',
    ];

    /**
    * The attributes that appended to the model.
    *
    * @var array
    */
    protected $appends = [
        'value'
    ];

    /**
    * Returns the value of the property. Returns the default value
    * if no value has been defined.
    *
    * @return array
    */
    public function getValueAttribute()
    {
        if ($this->attributes['type'] == 'SCHEMA') {
            $value = $this->pivot->value ?? false;
                  
            if (!$value) {
                return collect($this->default)->keyBy('key')->transform(function ($value) {
                    return $value->default;
                })->all();
            }
        } else {
            return $this->pivot->value ?? $this->default;
        }
    }

    /**
    * Mutate the key to always be uppercase.
    *
    * @param  string  $value
    * @return array
    */
    public function setKeyAttribute($value)
    {
        $this->attributes['key'] = strtoupper($value);
    }

    /**
    * Mutate the value relative to the type..
    *
    * @param  string  $value
    * @return array
    */
    public function setDefaultAttribute($value)
    {
        switch ($this->attributes['type']) {
          case 'JSON':
              $this->attributes['default'] = json_encode($value);
              break;
              
          case 'SCHEMA':
              $this->attributes['default'] = json_encode($value);
              break;

          case 'INT':
          case 'INTEGER':
              $this->attributes['default'] = intval($value);
              break;

          case 'BOOL':
          case 'BOOLEAN':
              $this->attributes['default'] = boolval($value);
              break;

          default:
              $this->attributes['default'] = $value;
              break;
        }
    }

    /**
    * Mutate the type to always be uppercase.
    *
    * @param  string  $value
    * @return array
    */
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtoupper($value);
    }

    /**
    * Mutate the default value according to its type.
    *
    * @param  string  $value
    * @return array
    */
    public function getDefaultAttribute($value)
    {
        switch ($this->attributes['type']) {
          case 'JSON':
              $value = !is_array($value) ? json_decode($value) : $value;
              break;
              
          case 'SCHEMA':
              $value = !is_array($value) ? json_decode($value) : $value;
              break;

          case 'INT':
          case 'INTEGER':
              $value = intval($value);
              break;

          case 'BOOL':
          case 'BOOLEAN':
              $value = boolval($value);
              break;

          default:
              break;
        }

        return $value;
    }

    /**
    * Scope to only return properties that target an array of items.
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param array|string $targets
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function scopeTargetting($query, $targets = [])
    {
        return $query->whereJsonContains('targets', $targets);
    }
}
