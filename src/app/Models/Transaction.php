<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['item_id','buyer_id','seller_id','status'];

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function buyer() {
        return $this->belongsTo(User::class,'buyer_id');
    }

    public function seller() {
        return $this->belongsTo(User::class,'seller_id');
    }

    public function chats() {
        return $this->hasMany(Chat::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function latestChat(){
        return $this->hasOne(Chat::class)->latestOfMany();
    }
}
