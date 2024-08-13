<?php

namespace App\Models\Sale;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "payment_method",
        "currency_total",
        "currency_payment",
        "discount",
        "subtotal",
        "total",
        "dolar_price",
        "description",
        "n_transaction",
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function sale_details(){
        return $this->hasMany(SaleDetail::class);
    }

    public function sale_address(){
        return $this->hasOne(SaleAddress::class);
    }
}
