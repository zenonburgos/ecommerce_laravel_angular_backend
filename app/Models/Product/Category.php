<?php

namespace App\Models\Product;

use App\Models\Discount\DiscountCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "name",
        "icon",
        "imagen",
        "category_second_id",
        "category_third_id",
        "position",
        "type_category",
        "state"
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["created_at"] = Carbon::now();
    }


    public function setUpdatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function category_second(){ //trae uno
        return $this->belongsTo(Category::class, "category_second_id");
    }

    public function category_third(){
        return $this->belongsTo(Category::class, "category_third_id");
    }

    public function category_seconds(){ //trae varios
        return $this->hasMany(Category::class, "category_second_id");
    }

    public function product_categorie_firsts(){
        return $this->hasMany(Product::class, "category_first_id");
    }

    public function product_categorie_seconds(){
        return $this->hasMany(Product::class, "category_second_id");
    }

    public function product_categorie_thirds(){
        return $this->hasMany(Product::class, "category_third_id");
    }

    public function discount_categories(){
        return $this->hasMany(DiscountCategory::class, "category_id");
    }
}
