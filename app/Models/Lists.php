<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'count_tasks',
        'is_completed',
        'is_closed',
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            UserList::create(['user_id' => Auth()->user()->id, 'list_id' => $model->id]);
        });
    }

    public function checkAttribute($attr = '', $operator = '', $value = '')
    {
        if (in_array($attr, $this->getFillable())) {
            return [$attr, $operator, $value];
        }

        return [[]];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'list_id');
    }
}
