<?php

namespace App\Http\Resources\Ecommerce\Product;

use App\Models\Product\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductEcommerceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $discount_g = null;
        
        $discount_collect = collect([]);

        $discount_product = $this->resource->discount_product; // (getDiscountProductAttribute = discount_product ya que la mayúscula indica caracter _)
        if($discount_product){
            $discount_collect->push($discount_product);
        }

        $discount_category = $this->resource->discount_category;
        if($discount_category){
            $discount_collect->push($discount_category);
        }

        $discount_brand = $this->resource->discount_brand;
        if($discount_brand){
            $discount_collect->push($discount_brand);
        }

        if($discount_collect->count() > 0){
            $discount_g = $discount_collect->sortByDesc("discount")->values()->all()[0];
        }

        $variation_collect = collect([]);
        foreach ($this->resource->variations->groupBy("attribute_id") as $key => $variation_t) {
            $variation_collect->push([
                "attribute_id" => $variation_t[0]->attribute_id,
                "attribute" => $variation_t[0]->attribute ? [
                    "name" => $variation_t[0]->attribute->name,
                    "type_attribute" => $variation_t[0]->attribute->type_attribute,
                ] : NULL,
                "variations" => $variation_t->map(function($variation) {
                    
                    return [
                        "id" => $variation->id,
                        "product_id" => $variation->product_id,
                        "attribute_id" => $variation->attribute_id,
                        "attribute" => $variation->attribute ? [
                            "name" => $variation->attribute->name,
                            "type_attribute" => $variation->attribute->type_attribute,
                        ] : NULL,
                        "propertie_id" => $variation->propertie_id,
                        "propertie" => $variation->propertie ? [
                            "name" => $variation->propertie->name,
                            "code" => $variation->propertie->code,
                        ] : NULL,
                        "value_add" => $variation->value_add,
                        "add_price" => $variation->add_price,
                        "stock" => $variation->stock,
                        "subvariation" => $variation->variation_children->count() > 0 ? [
                            "attribute_id" => $variation->variation_children->first()->attribute_id,
                            "attribute" => $variation->variation_children->first()->attribute ? [
                                "name" => $variation->variation_children->first()->attribute->name,
                                "type_attribute" => $variation->variation_children->first()->attribute->type_attribute,
                            ] : NULL,
                        ] : null,
                        "subvariations" => $variation->variation_children->map(function($subvariation) {
                            return [
                                "id" => $subvariation->id,
                                "product_id" => $subvariation->product_id,
                                "attribute_id" => $subvariation->attribute_id,
                                "attribute" => $subvariation->attribute ? [
                                    "name" => $subvariation->attribute->name,
                                    "type_attribute" => $subvariation->attribute->type_attribute,
                                ] : NULL,
                                "propertie_id" => $subvariation->propertie_id,
                                "propertie" => $subvariation->propertie ? [
                                    "name" => $subvariation->propertie->name,
                                    "code" => $subvariation->propertie->code,
                                ] : NULL,
                                "value_add" => $subvariation->value_add,
                                "add_price" => $subvariation->add_price,
                                "stock" => $subvariation->stock,
                            ];
                        }),
                    ];
                })
            ]);
        }

        $tags_parse = [];

        foreach (($this->resource->tags ? json_decode($this->resource->tags,true) : []) as $key => $tag) {
            array_push($tags_parse,$tag["item_text"]);
        }

        return [
            "id" => $this->resource->id,
            "title" => $this->resource->title,
            "slug" => $this->resource->slug,
            "sku" => $this->resource->sku,
            "barcode" => $this->resource->barcode,
            "price" => $this->resource->price,
            "promotion_price" => $this->resource->promotion_price,
            "resumen" => $this->resource->resumen,
            "imagen" => env("APP_URL")."storage/".$this->resource->imagen,
            "state" => $this->resource->state,
            "description" => $this->resource->description,
            "tags" => $this->resource->tags ? json_decode($this->resource->tags) : [],
            "tags_parse" => $tags_parse,
            "brand_id" => $this->resource->brand_id,
            "brand" => $this->resource->brand ? [
                "id" => $this->resource->brand->id,
                "name" => $this->resource->brand->name,
            ] : NULL,
            "category_first_id" => $this->resource->category_first_id,
            "category_first" => $this->resource->category_first ? [
                "id" => $this->resource->category_first->id,
                "name" => $this->resource->category_first->name,
            ] : NULL,
            "category_second_id" => $this->resource->category_second_id,
            "category_second" => $this->resource->category_second ? [
                "id" => $this->resource->category_second->id,
                "name" => $this->resource->category_second->name,
            ] : NULL,
            "category_third_id" => $this->resource->category_third_id,
            "category_third" => $this->resource->category_third ? [
                "id" => $this->resource->category_third->id,
                "name" => $this->resource->category_third->name,
            ] : NULL,
            "stock"  => $this->resource->stock,
            "created_at" => $this->resource->created_at->format("Y-m-d h:i:s"),
            "images" => $this->resource->images->map(function($image) {
                return [
                    "id" => $image->id,
                    "imagen" => env("APP_URL")."storage/".$image->imagen,
                ];
            }),
            "discount_g" => $discount_g,
            "variations" => $variation_collect,
            "specifications" => $this->resource->specifications->map(function($specifications) {
                return [
                    "id" => $specifications->id,
                    "product_id" => $specifications->product_id,
                    "attribute_id" => $specifications->attribute_id,
                    "attribute" => $specifications->attribute ? [
                        "name" => $specifications->attribute->name,
                        "type_attribute" => $specifications->attribute->type_attribute,
                    ] : NULL,
                    "propertie_id" => $specifications->propertie_id,
                    "propertie" => $specifications->propertie ? [
                        "name" => $specifications->propertie->name,
                        "code" => $specifications->propertie->code,
                    ] : NULL,
                    "value_add" => $specifications->value_add,
                ];
            })
        ];
    }
}

// $this->resource->variations->map(function($variation) {
        //     return [
        //         "id" => $variation->id,
        //         "product_id" => $variation->product_id,
        //         "attribute_id" => $variation->attribute_id,
        //         "attribute" => $variation->attribute ? [
        //             "name" => $variation->attribute->name,
        //             "type_attribute" => $variation->attribute->type_attribute,
        //         ] : NULL,
        //         "propertie_id" => $variation->propertie_id,
        //         "propertie" => $variation->propertie ? [
        //             "name" => $variation->propertie->name,
        //             "code" => $variation->propertie->code,
        //         ] : NULL,
        //         "value_add" => $variation->value_add,
        //         "add_price" => $variation->add_price,
        //         "stock" => $variation->stock,
        //         "variations" => $variation->variation_children->map(function($subvariation) {
        //             return [
        //                 "id" => $subvariation->id,
        //                 "product_id" => $subvariation->product_id,
        //                 "attribute_id" => $subvariation->attribute_id,
        //                 "attribute" => $subvariation->attribute ? [
        //                     "name" => $subvariation->attribute->name,
        //                     "type_attribute" => $subvariation->attribute->type_attribute,
        //                 ] : NULL,
        //                 "propertie_id" => $subvariation->propertie_id,
        //                 "propertie" => $subvariation->propertie ? [
        //                     "name" => $subvariation->propertie->name,
        //                     "code" => $subvariation->propertie->code,
        //                 ] : NULL,
        //                 "value_add" => $subvariation->value_add,
        //                 "add_price" => $subvariation->add_price,
        //                 "stock" => $subvariation->stock,
        //             ];
        //         })
        //     ];
        // })