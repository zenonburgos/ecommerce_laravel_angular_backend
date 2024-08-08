<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $brands = Brand::where("name", "like", "%".$search."%")->orderBy("id", "desc")->paginate(25);

        return response()->json([
            "total" => $brands->total(),
            "brands" => $brands->map(function($brand) {
                return [
                    "id" => $brand->id,
                    "name" => $brand->name,
                    "state" => $brand->state,
                    "created_at" => $brand->created_at->format("Y-m-d h:i:s"),
                    
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isValida = Brand::where("name", $request->name)->first();
        if($isValida){ //Si ya existe un nombre de atributo...
            return response()->json(["message" => 403]);
        }
        $brand = Brand::create($request->all());

        return response()->json([
            "message" => 200,
            "brand" => [
                "id" => $brand->id,
                "name" => $brand->name,
                "state" => $brand->state,
                "created_at" => $brand->created_at->format("Y-m-d h:i:s"),
                
            ],
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $isValida = Brand::where("id", "<>" ,$id)->where("name", $request->name)->first();
        if($isValida){ //Si ya existe un nombre de atributo...
            return response()->json(["message" => 403]);
        }
        $brand = Brand::findOrFail($id);
        $brand->update($request->all());
        
        return response()->json([
            "message" => 200,
            "brand" => [
                "id" => $brand->id,
                "name" => $brand->name,
                "state" => $brand->state,
                "created_at" => $brand->created_at->format("Y-m-d h:i:s"),
                
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        if($brand->products->count() > 0){
            return response()->json(["message" => 403, "message_text" => "LA MARCA ESTÁ RELACIONADO CON UNO O MÁS PRODUCTOS."]);
        }
        $brand->delete(); //IMPORTANTE VALIDACION
        return response()->json([
            "message" => 200,
        ]);
    }
}
