<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    use HasFactory;
    protected $table = 'followers';
    protected $guarded = [];

    public function followers(){
        return $this->belongsTo(User::class, 'followed_id');
    }
    public function following(){
        return $this->belongsTo(User::class, 'follower_id');
    }
}
