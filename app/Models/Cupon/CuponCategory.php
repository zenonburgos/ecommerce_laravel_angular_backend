<?php

namespace App\Models\Cupon;

use App\Models\Product\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuponCategory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "cupon_id",
        "category_id",
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
