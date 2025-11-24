<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'client_token',
        'pressing_token',
        'user_token',
        'reference',
        'type',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'payment_method',
        'payment_status',
        'delivery_status',
        'deposit_date',
        'delivery_date'
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
     * Get the client that owns the order.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_token', 'token');
    }
    
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_token', 'token');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_token', 'token');
    }

    /**
     * Un dépôt a été créé par un seul utilisateur (gestionnaire).
     * Relation Many-to-One.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        
        return $this->belongsTo(User::class, 'user_token', 'token');
    }
}