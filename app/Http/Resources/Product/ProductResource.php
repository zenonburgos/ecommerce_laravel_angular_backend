<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            })
        ];
    }
}
