<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const ROLE_ADMIN = '1';
    const ROLE_TEACHER = '3';
    const ROLE_CORRECTOR = '2';


    public static function getRoles() {

        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_TEACHER => 'Переводчик',
            self::ROLE_CORRECTOR => 'Корректор',
        ];
    }

    public static function getRoleName($role) {
        $roles = self::getRoles();
        return $roles[$role] ?? 'Не подтвержден';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function translations()
    {
        return $this->hasMany(Translate::class, 'user_id', 'id');
    }
}
