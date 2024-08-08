<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceCollection;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceResource;
use App\Models\Discount\Discount;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Slider;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    public function home(Request $request){
        $slider_principal = Slider::where("state",1)->where("type_slider",1)->orderBy("id","desc")->get();

        $categories_randoms = Category::withCount(["product_categorie_firsts"])
                                    ->where("category_second_id",NULL)
                                    ->where("category_third_id",NULL)
                                    ->inRandomOrder()->limit(5)->get();
       

        $product_trending_new = Product::where("state",2)->inRandomOrder()->limit(8)->get();
        $product_trending_featured = Product::where("state",2)->inRandomOrder()->limit(8)->get();
        $product_trending_top_sellers = Product::where("state",2)->inRandomOrder()->limit(8)->get();
        
        $sliders_secundario = Slider::where("state",1)->where("type_slider",2)->orderBy("id","asc")->get();
        $product_electronics = Product::where("state",2)->where("category_first_id", 1)->inRandomOrder()->limit(6)->get();
        
        $products_carrusel = Product::where("state",2)->whereIn("category_first_id", $categories_randoms->pluck("id"))->inRandomOrder()->limit(10)->get();        
        $sliders_products = Slider::where("state",1)->where("type_slider",3)->orderBy("id","asc")->get();

        $product_last_discounts = Product::where("state",2)->inRandomOrder()->limit(3)->get();
        $product_last_featured = Product::where("state",2)->inRandomOrder()->limit(3)->get();
        $product_last_selling = Product::where("state",2)->inRandomOrder()->limit(3)->get();


        date_default_timezone_set("America/El_Salvador");
        $DISCOUNT_FLASH = Discount::where("type_campaign",2)->where("state",1)
                            ->where("start_date","<=",today())
                            ->where("end_date",">=",today())
                            ->first();
        
        $DISCOUNT_FLASH_PRODUCTS = collect([]);

        if($DISCOUNT_FLASH){
            foreach ($DISCOUNT_FLASH->products as $key => $aux_product) {
                $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($aux_product->product));
            }
            foreach ($DISCOUNT_FLASH->categories as $key => $aux_category) {
                $products_of_categories = Product::where("state",2)->where("category_first_id",$aux_category->category_id)->get();
                foreach ($products_of_categories as $key => $product) {
                    $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($product));
                }
            }
            foreach ($DISCOUNT_FLASH->brands as $key => $aux_brand) {
                $products_of_brands = Product::where("state",2)->where("brand_id",$aux_brand->brand_id)->get();
                foreach ($products_of_brands as $key => $product) {
                    $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($product));
                }
            }
            //Sep 30 2024 20:20:22
            $DISCOUNT_FLASH->end_date_format = Carbon::parse($DISCOUNT_FLASH->end_date)->addDays(1)->format('M d Y H:i:s');
        }
        
        return response()->json([
            "slider_principal" => $slider_principal->map(function($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "subtitle" => $slider->subtitle,
                    "label" => $slider->label,
                    "imagen" => $slider->imagen ? env("APP_URL")."storage/".$slider->imagen : NULL,
                    "link" => $slider->link,
                    "state" => $slider->state,
                    "color" => $slider->color,
                    "type_slider => $slider->type_slider",
                    "price_original" => $slider->price_original,
                    "price_campaign" => $slider->price_campaign,
                ];
            }),
            "categories_randoms" => $categories_randoms->map(function($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "products_count" => $category->product_categorie_firsts_count,
                    "imagen" => env("APP_URL")."storage/".$category->imagen,
                ];
            }),
            
            "product_trending_new" => ProductEcommerceCollection::make($product_trending_new),
            "product_trending_featured" => ProductEcommerceCollection::make($product_trending_featured),
            "product_trending_top_sellers" => ProductEcommerceCollection::make($product_trending_top_sellers),
            "sliders_secundario" => $sliders_secundario->map(function($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "subtitle" => $slider->subtitle,
                    "label" => $slider->label,
                    "imagen" => $slider->imagen ? env("APP_URL")."storage/".$slider->imagen : NULL,
                    "link" => $slider->link,
                    "state" => $slider->state,
                    "color" => $slider->color,
                    "type_slider => $slider->type_slider",
                    "price_original" => $slider->price_original,
                    "price_campaign" => $slider->price_campaign,
                ];
            }),
            "product_electronics" => ProductEcommerceCollection::make($product_electronics),
            "products_carrusel" => ProductEcommerceCollection::make($products_carrusel),
            "sliders_products" => $sliders_products->map(function($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "subtitle" => $slider->subtitle,
                    "label" => $slider->label,
                    "imagen" => $slider->imagen ? env("APP_URL")."storage/".$slider->imagen : NULL,
                    "link" => $slider->link,
                    "state" => $slider->state,
                    "color" => $slider->color,
                    "type_slider => $slider->type_slider",
                    "price_original" => $slider->price_original,
                    "price_campaign" => $slider->price_campaign,
                ];
            }),
            "product_last_discounts" => ProductEcommerceCollection::make($product_last_discounts),
            "product_last_featured" => ProductEcommerceCollection::make($product_last_featured),
            "product_last_selling" => ProductEcommerceCollection::make($product_last_selling),
            "discount_flash" => $DISCOUNT_FLASH,
            "discount_flash_products" => $DISCOUNT_FLASH_PRODUCTS,
        ]);
    }

    public function menus(){
        $categories_menus = Category::where("category_second_id",NULL)
        ->where("category_third_id",NULL)
        ->orderBy("position", "desc")
        ->get();

        return response()->json([
            
            "categories_menus" => $categories_menus->map(function($department) {
                return [
                    "id" => $department->id,
                    "name" => $department->name,
                    "icon" => $department->icon,
                    "categories" => $department->category_seconds->map(function($category) {
                        return [
                            "id" => $category->id,
                            "name" => $category->name,
                            "imagen" => $category->imagen ? env("APP_URL")."storage/".$category->imagen : NULL,
                            "subcategories" => $category->category_seconds->map(function($subcategory) {
                                return [
                                    "id" => $subcategory->id,
                                    "name" => $subcategory->name,
                                    "imagen" => $subcategory->imagen ? env("APP_URL")."storage/".$subcategory->imagen : NULL,
                                ];
                            })
                        ];
                    })
                ];
            }),
            
        ]);
    }

    public function show_product(Request $request,$slug){
        $campaign_discount = $request->get("campaign_discount");
        $discount = null;
        if($campaign_discount){
            $discount = Discount::where("code", $campaign_discount)->first();
        }

        $product = Product::where("slug",$slug)->where("state",2)->first();

        if(!$product){
            return response()->json([
                "message" => 403,
                "message_text" => "EL PRODUCTO NO EXISTE."
            ]);
        }

        $product_relateds = Product::where("category_first_id",$product->category_first_id)->where("state",2)->get();

        return response()->json([
            "message" => 200,
            "product" => ProductEcommerceResource::make($product),
            "product_relateds" => ProductEcommerceCollection::make($product_relateds),
            "discount_campaign" => $discount,
        ]);
    }
}
