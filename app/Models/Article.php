<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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


    /**
     * Get the route key for the model.
     *
     * @return string
     */
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


