<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['date', 'amount', 'type', 'category', 'description'];
    
    const TYPE_INCOME = 1;
    const TYPE_EXPENSE = 2;

    public function getTypeTextAttribute()
    {
        return $this->type == self::TYPE_INCOME ? '收入' : '支出';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
