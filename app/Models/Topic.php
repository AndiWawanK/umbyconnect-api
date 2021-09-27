<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;
    protected $table = 'topic';
    protected $guarded = [];

    public function thread(){
        return $this->hasMany(Thread::class);
    }

}
