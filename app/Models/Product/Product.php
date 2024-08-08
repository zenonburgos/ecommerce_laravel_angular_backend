<?php

namespace App\Models\Product;

use App\Models\Discount\DiscountBrand;
use App\Models\Discount\DiscountCategory;
use App\Models\Discount\DiscountProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "title",
        "slug",
        "sku",
        "barcode",
        "price",
        "promotion_price",
        "resumen",
        "imagen",
        "state",
        "description",
        "tags",
        "brand_id",
        "category_first_id",
        "category_second_id",
        "category_third_id",
        "stock",
    ];

    public function setCreatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value){
        date_default_timezone_set('America/El_Salvador');
        $this->attributes["updated_at"] = Carbon::now();
    }

    public function category_first(){
        return $this->belongsTo(Category::class, "category_first_id");
    }

    public function category_second(){
        return $this->belongsTo(Category::class, "category_second_id");
    }

    public function category_third(){
        return $this->belongsTo(Category::class, "category_third_id");
    }

    public function brand(){
        return $this->belongsTo(Brand::class, "brand_id");
    }

    public function images(){
        return $this->hasMany(ProductImage::class, "product_id");
    }

    public function variations(){
        return $this->hasMany(ProductVariation::class, "product_id")->where("product_variation_id",NULL);
    }

    public function specifications(){
        return $this->hasMany(ProductSpecification::class, "product_id");
    }

    public function discount_products(){
        return $this->hasMany(DiscountProduct::class, "product_id");
    }

    //discount_category
    public function getDiscountCategoryAttribute(){
        date_default_timezone_set('America/El_Salvador');
        $discount = null;
        foreach ($this->discount_products as $key => $discount_product) {
            if($discount_product->discount && $discount_product->discount->type_campaign == 1 && 
            $discount_product->discount->state == 1){
                if(Carbon::now()->between($discount_product->discount->start_date,Carbon::parse(
                    $discount_product->discount->end_date)->addDays(1))){
                    $discount = $discount_product->discount;
                    break;
                }
            }
        }
        return $discount;
    }

    public function getDiscountProductAttribute(){
        date_default_timezone_set('America/El_Salvador');
        $discount = null;
        foreach ($this->category_first->discount_categories as $key => $discount_category) {
            if($discount_category->discount && $discount_category->discount->type_campaign == 1 && 
            $discount_category->discount->state == 1){
                if(Carbon::now()->between($discount_category->discount->start_date,Carbon::parse(
                    $discount_category->discount->end_date)->addDays(1))){
                    $discount = $discount_category->discount;
                    break;
                }
            }
        }
        return $discount;
        
    }

    public function getDiscountBrandAttribute(){
        date_default_timezone_set('America/El_Salvador');
        date_default_timezone_set('America/El_Salvador');
        $discount = null;
        foreach ($this->brand->discount_brands as $key => $discount_brand) {
            if($discount_brand->discount && $discount_brand->discount->type_campaign == 1 && 
            $discount_brand->discount->state == 1){
                if(Carbon::now()->between($discount_brand->discount->start_date,Carbon::parse(
                    $discount_brand->discount->end_date)->addDays(1))){
                    $discount = $discount_brand->discount;
                    break;
                }
            }
        }
        return $discount;
    }

    public function scopeFilterAdvanceProduct($query, $search, $category_first_id, $category_second_id, $category_third_id, $brand_id){

        if($search){
            $query->where('title', 'like', "%".$search."%");
        }

        if($category_first_id){
            $query->where("category_first_id", $category_first_id);
        }

        if($category_second_id){
            $query->where("category_second_id", $category_second_id);
        }

        if($category_third_id){
            $query->where("category_third_id", $category_third_id);
        }

        if($brand_id){
            $query->where("brand_id", $brand_id);
        }

        return $query;
    }
    
}
