<?php

namespace App\Http\Controllers\Admin\Cupon;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cupon\CuponCollection;
use App\Http\Resources\Cupon\CuponResource;
use App\Models\Cupon\Cupon;
use App\Models\Cupon\CuponBrand;
use App\Models\Cupon\CuponCategory;
use App\Models\Cupon\CuponProduct;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CuponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cupones = Cupon::where("code", "like", "%".$request->search."%")->orderBy("id", "desc")->paginate(25);

        return response()->json([
            "total" => $cupones->total(),
            "cupones" => CuponCollection::make($cupones),
        ]);
    }

    public function config(){
        $products = Product::where("state", 2)->orderBy("id", "desc")->get();

        $categories = Category::where("state",1)->where("category_second_id", NULL)
                                ->where("category_third_id", NULL)
                                ->orderBy("id", "desc")->get();

        $brands = Brand::where("state",1)->orderBy("id", "desc")->get();

        return response()->json([
            "products" => $products->map(function($product) {
                return [
                    "id" => $product->id,
                    "title" => $product->title,
                    "slug" => $product->slug,
                    "imagen" => env("APP_URL")."storage/".$product->imagen,
                ];
            }),
            "categories" => $categories->map(function($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "imagen" => env("APP_URL")."storage/".$category->imagen,
                ];
            }),
            "brands" => $brands->map(function($brand) {
                return [
                    "id" => $brand->id,
                    "name" => $brand->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // product_selected, category_selected, brand_selected
        $IS_EXIST = Cupon::where("code", $request->code)->first();
        if($IS_EXIST){
            return response()->json(["message" => 403, "message_text" => "EL CUPÓN YA FUE UTILIZADO."]);
        }

        $CUPON = Cupon::create($request->all());

        foreach ($request->product_selected as $key => $product_selec) {
            CuponProduct::create([
                "cupon_id" => $CUPON->id,
                "product_id" => $product_selec["id"],
            ]);
        }

        foreach ($request->category_selected as $key => $category_selec) {
            CuponCategory::create([
                "cupon_id" => $CUPON->id,
                "category_id" => $category_selec["id"],
            ]);
        }

        foreach ($request->brand_selected as $key => $brand_selec) {
            CuponBrand::create([
                "cupon_id" => $CUPON->id,
                "brand_id" => $brand_selec["id"],
            ]);
        }

        return response()->json(["message" => 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $CUPON = Cupon::findOrFail($id);
        return response()->json(["cupon" => CuponResource::make($CUPON)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // product_selected , categorie_selected , brand_selected
        $IS_EXIST = Cupon::where("code",$request->code)->where("id","<>",$id)->first();
        if($IS_EXIST){
            return response()->json(["message" => 403,"message_text" => "EL CUPON YA EXISTE, DIGITE OTRO POR FAVOR"]);
        } 

        $CUPON = Cupon::findOrFail($id);
        $CUPON->update($request->all());

        foreach ($CUPON->categories as $key => $category) {
            $category->delete();
        }

        foreach ($CUPON->products as $key => $product) {
            $product->delete();
        }

        foreach ($CUPON->brands as $key => $brand) {
            $brand->delete();
        }

        foreach ($request->product_selected as $key => $product_selec) {
            CuponProduct::create([
                "cupon_id" => $CUPON->id,
                "product_id" => $product_selec["id"],
            ]);
        }
        foreach ($request->category_selected as $key => $category_selec) {
            CuponCategory::create([
                "cupon_id" => $CUPON->id,
                "category_id" => $category_selec["id"],
            ]);
        }
        foreach ($request->brand_selected as $key => $brand_selec) {
            //Log::info('Brand selected:', ['brand' => $brand_selec]);
            CuponBrand::create([
                "cupon_id" => $CUPON->id,
                "brand_id" => $brand_selec["id"],
            ]);
        }

        return response()->json(["message" => 200]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CUPON = Cupon::findOrFail($id);
        $CUPON->delete();
        //CUANDO HAY UNA COMPRA RELACIONADA CON EL CUPON YA NO SE PUEDE ELIMINAR
        return response()->json(["message" => 200]);
    }
}
