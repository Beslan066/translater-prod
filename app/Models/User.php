<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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

    protected $dates = ['last_seen'];


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


public function sentencesWithStatusOne()
{
    return Sentence::whereIn('id', function ($query) {
        $query->select('sentence_id')
              ->from('translates')
              ->where('user_id', $this->id);
    })->where('status', 1)->with(['translations' => function ($query) {
        $query->where('user_id', $this->id);
    }])->get();
}

public function sentencesWithStatusTwo()
{
    return Sentence::whereIn('id', function ($query) {
        $query->select('sentence_id')
              ->from('translates')
              ->where('user_id', $this->id);
    })->where('status', 2)->with(['translations' => function ($query) {
        $query->where('user_id', $this->id);
    }])->get();
}

}
