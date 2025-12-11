<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Paie extends Model
{
    use HasFactory;

    // --- Configuration UUID ---
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    // -------------------------

    protected $fillable = [
        'token', 'month', 'year', 'status', 'net_to_pay',
        'total_base_salary', 'total_advantages', 'total_primes', 
        'total_fiscal_retentions', 'total_social_retentions',
        'total_patronal_contributions', 'total_fiscal_charges', 
        'total_exceptional_retention',
        'pressing_token', 'user_token'
    ];
    
    protected $casts = [
        'total_base_salary' => 'decimal:2',
        'net_to_pay' => 'decimal:2',
        // Ajoutez les autres totaux si vous voulez qu'ils soient auto-castés
    ];

    // --- Relations ---

    /**
     * Récupère tous les détails de paie (ItemPaie) associés à cet en-tête de paie.
     */
    public function items()
    {
        return $this->hasMany(ItemPaie::class, 'paie_token', 'token');
    }
}