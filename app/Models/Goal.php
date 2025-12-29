<?php

// app/Models/Goal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Ajout du trait
use App\Models\User;

class Goal extends Model
{
    use HasFactory, HasUuids;

    // Définir la clé primaire comme étant 'token' (UUID)
    protected $primaryKey = 'token'; 
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'pressing_token',
        'user_token', // Renommé 'user_token'
        'type',
        'periodicity',
        'start_date',
        'end_date',
        'target_value',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_value' => 'float',
    ];

    // Relation pour l'utilisateur ciblé (utilisant user_token)
    public function user()
    {
        // Supposons que la clé primaire de la table 'users' est 'token'
        return $this->belongsTo(User::class, 'user_token', 'token'); 
    }
    public function getRouteKeyName()
    {
        return 'token';
    }
}
