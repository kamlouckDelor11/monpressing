<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public function getRouteKeyName()
    {
        return 'token';
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'phone',
        'address',
        'user_token',
        'pressing_token',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function pressing()
    {
        return $this->belongsTo(Pressing::class, 'pressing_token', 'token');
    }

    public function user()
    {
        return $this->belongsTo(user::class, 'user_token', 'token');
    }
}
