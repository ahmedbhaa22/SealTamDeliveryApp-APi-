<?php
namespace App\Http\Mappers;

class ProductMapper
{
    public static function singleProductMapper($product){
        $images = [$product->image];
        if($product->image1 != null) array_push($product->image1);
        if($product->image2 != null) array_push($product->image2);
        if($product->image3 != null) array_push($product->image3);
        if($product->image4 != null) array_push($product->image4);

        return [
            "product_id" =>$product->product_id,
            "Category" => $product->category_name,
            "manufacture"=> $product->manufacture_name,
            "product_name"=> $product->product_name,
            "product_description"=> $product->product_description,
            "price"=> $product->price,
            "price_after_discount"=> $product->old_price,
            "images"=>$images
        ];
    }
    public static function ProductGridMapper($product){
        $images = [$product->image];
        return [
            "product_id" =>$product->product_id,
            "Category" => $product->category_name,
            "manufacture"=> $product->manufacture_name,
            "product_name"=> $product->product_name,
            "price"=> $product->price,
            "price_before_discount"=> $product->old_price,
            "image"=>$product->image
        ];
    }
    public static function ProductCartGridMapper($product){
        $images = [$product->image];
        return [
            "product_id" =>$product->product_id,
            "Category" => $product->category_name,
            "manufacture"=> $product->manufacture_name,
            "product_name"=> $product->product_name,
            "price"=> $product->price,
            "price_before_discount"=> $product->old_price,
            "Quantity"=> $product->Quantity,
            "image"=>$product->image
        ];
    }
    public static function CategorySliderMapper($slider){
        return [
            "image" =>$slider->slider_image,
        ];
    }
}
