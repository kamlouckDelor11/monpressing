<?php

namespace App\Models;

use App\Models\Depense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;


class ItemSpens extends Model
{
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['token', 'spens_token', 'depenses_token', 'user_token', 'pressing_token', 'description', 'amount_spens', 'date_spens', 'status'];
    protected $dates = ['date_spens'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relation: Une transaction appartient à une catégorie
    public function spensCategory(): BelongsTo
    {
        return $this->belongsTo(Spens::class, 'spens_token', 'token');
    }
    

}
