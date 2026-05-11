<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sentence extends Model
{
    use HasFactory;


    protected $fillable = [
      'id',
      'sentence',
      'status',
      'author',
      'locked_by',
      'price',
      'delayed',
      'reviewed_by',
      'reviewed_at'

    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Статусы предложений
    const STATUS_PENDING = 1;      // На проверке (ожидает корректора)
    const STATUS_COMPLETED = 2;    // Переведено/подтверждено

    public  function author() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function translations()
    {
        return $this->hasMany(Translate::class, 'sentence_id');
    }

    // Связь с корректором, который подтвердил
    public function reviewer() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeAvailableForCorrectors($query)
    {
        return $query->where('status', 1);
    }
}
