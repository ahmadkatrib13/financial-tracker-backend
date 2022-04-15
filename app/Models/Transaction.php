<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','amount','currency','date_time','status','recurring_id','category_id'];

    public function recurring()
    {
        return $this->belongsTo(Recurring::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
