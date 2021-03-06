<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    // use HasFactory, SoftDeletes;
    use HasFactory;

    protected $fillable = [
         'user_id','order_id','product_name','transaction_type','purchased_token','phone', 'amount', 'units', 
    ];
    public function user()
    {
       // return $this->belongsTo(User::class, 'user_id');
       return $this->belongsTo(User::class, 'user_id');
    }
    public function transactions()
    {
       // return $this->belongsTo(User::class, 'user_id');
       return $this->belongsTo(User::class, 'user_id');
    }
}
