<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'period',
        'total_income',
        'total_expense',
        'category_breakdown',
        'content'
    ];
}
