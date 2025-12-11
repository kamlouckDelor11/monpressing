<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItemPaie extends Model
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
        'token', 'paie_token', 'employe_token', 'base_salary', 'advantages', 'prime', 
        'fiscal_retention', 'social_retention', 'patronal_contribution', 
        'fiscal_charge', 'exceptional_retention', 'net_paid',
        'pressing_token', 'user_token'
    ];
    
    protected $casts = [
        'base_salary' => 'decimal:2',
        'net_paid' => 'decimal:2',
        // Ajoutez les autres montants si vous voulez qu'ils soient auto-castés
    ];

    // --- Relations ---

    /**
     * Récupère l'employé concerné par cet item de paie.
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_token', 'token');
    }

    /**
     * Récupère l'en-tête de paie (Paie) auquel cet item est rattaché.
     */
    public function paie()
    {
        return $this->belongsTo(Paie::class, 'paie_token', 'token');
    }
}