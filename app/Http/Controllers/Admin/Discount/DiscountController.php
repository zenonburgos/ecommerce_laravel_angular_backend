<?php

namespace App\Http\Controllers\Admin\Discount;

use App\Http\Controllers\Controller;
use App\Http\Resources\Discount\DiscountCollection;
use App\Http\Resources\Discount\DiscountResource;
use App\Models\Discount\Discount;
use App\Models\Discount\DiscountBrand;
use App\Models\Discount\DiscountCategory;
use App\Models\Discount\DiscountProduct;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::orderBy("id", "desc")->paginate(25);

        return response()->json([
            "total" => $discounts->total(),
            "discounts" => DiscountCollection::make($discounts),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ES VALIDAR QUE LOS PRODUCTOS O CATEGORI O MARCAS NO SE REGISTREN DENTRO DEL MISMO 
        // PERIODO DE TIEMPO
        // START_DATE Y END_DATE
        // discount_type / EL TIPO DE SEGMENTACIÓN QUE TIENE LA CAMPAÑA 
        // product_selected / category_selected / brand_selected
        // type_campaign
        if($request->discount_type == 1){//VALIDACION A NIVEL DE PRODUCTOS
            foreach ($request->product_selected as $key => $product_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("products",function($q) use($product_selected) {
                                                            $q->where("product_id",$product_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("products",function($q) use($product_selected) {
                                                            $q->where("product_id",$product_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "EL PRODUCTO ".$product_selected["title"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        if($request->discount_type == 2){//VALIDACION A NIVEL DE CATEGORIA
            foreach ($request->category_selected as $key => $category_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("categories",function($q) use($category_selected) {
                                                            $q->where("category_id",$category_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("categories",function($q) use($category_selected) {
                                                            $q->where("category_id",$category_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "LA CATEGORIA ".$category_selected["name"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        if($request->discount_type == 3){//VALIDACION A NIVEL DE MARCAS
            foreach ($request->brand_selected as $key => $brand_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("brands",function($q) use($brand_selected) {
                                                            $q->where("brand_id",$brand_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("brands",function($q) use($brand_selected) {
                                                            $q->where("brand_id",$brand_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "LA MARCA ".$brand_selected["name"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        $request->request->add(["code" => uniqid()]);
        $discount = Discount::create($request->all());

        foreach ($request->product_selected as $key => $product_selec) {
            DiscountProduct::create([
                "discount_id" => $discount->id,
                "product_id" => $product_selec["id"],
            ]);
        }

        foreach ($request->category_selected as $key => $category_selec) {
            DiscountCategory::create([
                "discount_id" => $discount->id,
                "category_id" => $category_selec["id"],
            ]);
        }

        foreach ($request->brand_selected as $key => $brand_selec) {
            DiscountBrand::create([
                "discount_id" => $discount->id,
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
        $discount = Discount::findOrFail($id);

        return response()->json([
            "discount" => DiscountResource::make($discount)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ES VALIDAR QUE LOS PRODUCTOS O CATEGORI O MARCAS NO SE REGISTREN DENTRO DEL MISMO 
        // PERIODO DE TIEMPO
        // START_DATE Y END_DATE
        // discount_type / EL TIPO DE SEGMENTACIÓN QUE TIENE LA CAMPAÑA 
        // product_selected / category_selected / brand_selected
        // type_campaign
        if($request->discount_type == 1){//VALIDACION A NIVEL DE PRODUCTOS
            foreach ($request->product_selected as $key => $product_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("products",function($q) use($product_selected) {
                                                            $q->where("product_id",$product_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("products",function($q) use($product_selected) {
                                                            $q->where("product_id",$product_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "EL PRODUCTO ".$product_selected["title"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        if($request->discount_type == 2){//VALIDACION A NIVEL DE CATEGORIA
            foreach ($request->category_selected as $key => $category_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("categories",function($q) use($category_selected) {
                                                            $q->where("category_id",$category_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("categories",function($q) use($category_selected) {
                                                            $q->where("category_id",$category_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "LA CATEGORIA ".$category_selected["name"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        if($request->discount_type == 3){//VALIDACION A NIVEL DE MARCAS
            foreach ($request->brand_selected as $key => $brand_selected) {
                $EXIST_DISCOUNT_START_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("brands",function($q) use($brand_selected) {
                                                            $q->where("brand_id",$brand_selected["id"]);
                                                        })->whereBetween("start_date",[$request->start_date,$request->end_date])
                                                        ->first();
                $EXIST_DISCOUNT_END_DATE = Discount::where("type_campaign",$request->type_campaign)
                                                        ->where("id","<>",$id)
                                                        ->where("discount_type",$request->discount_type)
                                                        ->whereHas("brands",function($q) use($brand_selected) {
                                                            $q->where("brand_id",$brand_selected["id"]);
                                                        })->whereBetween("end_date",[$request->start_date,$request->end_date])
                                                        ->first();

                if($EXIST_DISCOUNT_START_DATE || $EXIST_DISCOUNT_END_DATE){

                    return response()->json(["message" => 403,"message_text" => "LA MARCA ".$brand_selected["name"].
                    " YA SE ENCUENTRA EN UNA CAMPAÑA DE DESCUENTO QUE SERIA ".($EXIST_DISCOUNT_START_DATE ? '#'.$EXIST_DISCOUNT_START_DATE->code : '')
                    .($EXIST_DISCOUNT_END_DATE ? '#'.$EXIST_DISCOUNT_END_DATE->code : '')]);
                }   
            }
        }

        $discount = Discount::findOrFail($id);
        $discount->update($request->all());

        foreach ($discount->categories as $key => $category) {
            $category->delete();
        }

        foreach ($discount->products as $key => $product) {
            $product->delete();
        }

        foreach ($discount->brands as $key => $brand) {
            $brand->delete();
        }

        foreach ($request->product_selected as $key => $product_selec) {
            DiscountProduct::create([
                "discount_id" => $discount->id,
                "product_id" => $product_selec["id"],
            ]);
        }

        foreach ($request->category_selected as $key => $category_selec) {
            DiscountCategory::create([
                "discount_id" => $discount->id,
                "category_id" => $category_selec["id"],
            ]);
        }

        foreach ($request->brand_selected as $key => $brand_selec) {
            DiscountBrand::create([
                "discount_id" => $discount->id,
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
        $discount = Discount::findOrFail($id);
        $discount->delete();
        // SI PERTENECE A UNA VENTA NO DEBE ELIMINARSE
        return response()->json(["message" => 200]);
    }
}
