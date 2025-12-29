<?php

namespace App\Models;



// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'pressing_token',
        'name',
        'email',
        'phone',
        'profile',
        'password',
        'role',
        'status',
        'update_password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            
        ];
    }

    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function pressing()
    {
        return $this->belongsTo(Pressing::class, 'pressing_token', 'token');
    }

      public function goals()
    {
        return $this->hasMany(Goal::class, 'user_token', 'token');
    }

}
