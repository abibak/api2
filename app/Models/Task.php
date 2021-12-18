<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'list_id',
        'is_completed',
        'executor_user_id',
        'description',
        'urgency',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->executor_user_id = Auth()->user()->id;
        });
    }

    public function checkAttribute($attr = '', $operator = '', $value = '')
    {
        if (in_array($attr, $this->getFillable())) {
            return [$attr, $operator, $value];
        }

        return [[]];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'executor_user_id');
    }

    public function list()
    {
        return $this->belongsTo(Lists::class, 'list_id');
    }
}
