<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name','type'];

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
    public function recurring()
    {
        return $this->hasMany(Recurring::class);
    }

}
