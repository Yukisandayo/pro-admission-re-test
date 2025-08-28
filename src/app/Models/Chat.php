<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $fillable = ['transaction_id','user_id','message','is_read'];

    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function images() {
        return $this->hasMany(ChatImage::class);
    }
}
