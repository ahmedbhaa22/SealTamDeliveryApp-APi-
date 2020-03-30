<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Resources\Json\JsonResource;

class resturanMapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "lat"=>floatval($this->lat) ?? 0,
            "lng"=>floatval($this->lng) ?? 0,
            "name"=>$this->user->name,
            'icon'=>$this->category->icon ??'icons/chef.svg'
        ];
    }
}
