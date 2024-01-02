<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'time',
    ];
    
    public function taskUser()
    {
        return $this->hasMany(TaskUser::class,'question_id');
    }

}
