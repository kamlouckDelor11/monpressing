<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_token',
        'service_token',
        'article_token',
        'item_name',
        'item_type',
        'quantity',
        'unit_price',
        'total_price',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_token', 'token');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_token', 'token');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_token', 'token');
    }
}