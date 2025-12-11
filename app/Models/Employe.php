<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Employe extends Model
{
    use HasFactory;

    // --- Configuration UUID ---
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Assure la génération de l'UUID avant la création
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
        'token', 'full_name', 'function', 'base_salary', 'hiring_date',
        'pressing_token', 'user_token'
    ];
    
    protected $casts = [
        'base_salary' => 'decimal:2',
        'hiring_date' => 'date',
    ];

    // --- Relations ---

    /**
     * Récupère tous les détails de paie (ItemPaie) liés à cet employé.
     */
    public function paieItems()
    {
        return $this->hasMany(ItemPaie::class, 'employe_token', 'token');
    }
}