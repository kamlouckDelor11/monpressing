<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'price',
        'pressing_token',
        'user_token',
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

    public function getRouteKeyName()
    {
        return 'token';
    }


    /**
     * Get the user who created the client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_token', 'user_token');
    }
    
    /**
     * Get the pressing related to the client.
     */
    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class, 'pressing_token', 'token');
    }

    /**
     * Get the orders for the client.
     */
    // public function orders(): HasMany
    // {
    //     return $this->hasMany(Order::class, 'client_token', 'token');
    // }
}