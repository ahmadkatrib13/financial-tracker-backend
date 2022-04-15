<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurring extends Model
{
    use HasFactory;
    protected $fillable = ['amount','schedule','duration','start_date','next_run','end_date','category_id'];

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
    public function category()
    {
        return $this->belongsto(Category::class);
    }
}
