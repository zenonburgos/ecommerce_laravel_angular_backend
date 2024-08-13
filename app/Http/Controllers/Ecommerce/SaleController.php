<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Sale\SaleResource;
use App\Mail\SaleMail;
use App\Models\Sale\Cart;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleAddress;
use App\Models\Sale\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->request->add(["user_id" => auth("api")->user()->id]);
        $sale = Sale::create($request->all());

        $carts = Cart::where("user_id", auth("api")->user()->id)->get();

        foreach ($carts as $key => $cart) {
            $new_detail = [];
            $new_detail = $cart->toArray();
            $new_detail["sale_id"] = $sale->id;
            SaleDetail::create($new_detail);
            // LA ELIMINACIÓN DEL CARRITO DE COMPRA
        }
        $sale_addres = $request->sale_address;
        $sale_addres["sale_id"] = $sale->id;
        $sale_addresses = SaleAddress::create($sale_addres);
        // EL CORREO QUE LE DEBE LLEGAR AL CLIENTE CON LA COMPRA QUE ACABA DE REALIZAR
        $sale_new = Sale::findOrFail($sale->id);
        Mail::to(auth("api")->user()->email)->send(new SaleMail(auth("api")->user(),$sale_new));
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = Sale::where("n_transaction",$id)->first();

        return response()->json([
            "sale" => SaleResource::make($sale),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
