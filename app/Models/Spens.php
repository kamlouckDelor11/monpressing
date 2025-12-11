<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Spens extends Model
{
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['token', 'user_token', 'pressing_token', 'description', 'nature', 'default_amount'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relation: Une catégorie a plusieurs transactions/dépenses
    public function items(): HasMany
    {
        return $this->hasMany(ItemSpens::class, 'spens_token', 'token');
    }
}