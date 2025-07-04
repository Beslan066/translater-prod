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
      'delayed'

    ];

    public  function author() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function translations()
    {
        return $this->hasMany(Translate::class, 'sentence_id');
    }

    public function scopeAvailableForCorrectors($query)
    {
        return $query->where('status', 1);
    }
}
