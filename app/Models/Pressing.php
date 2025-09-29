<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pressing extends Model
{
     protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'token',
        'name',
        'phone',
        'email',
        'address',
        'subscription_plan',
        'subscription_expires_at',
        'logo',
        'niu',
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

    public function users()
    {
        return $this->hasMany(User::class, 'pressing_token', 'token');
    }
}
