<?php
namespace App\Http\Mappers;

class CategoryMapper
{
    public static function SmallCategoryMapper($category){
        return [
            "category_id" =>$category->category_id,
            "category_name" => $category->category_name,
            "category_image"=> $category->cat_image,
        ];
    }
    public static function CategorySliderMapper($slider){
        return [
            "image" =>$slider->slider_image,
        ];
    }
}
