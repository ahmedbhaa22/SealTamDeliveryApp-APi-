<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'name'=>$this->name,
            'email'=>$this->email,
            'image'=>$this->image,
            'frontId'=>$this->frontId,
            'backId'=>$this->backId,
            'telephone'=>$this->telephone,
            'lat'=>$this->lat,
            'lng'=>$this->lng,
            'canReceiveOrder'=>$this->canReceiveOrder,
            'availability'=>$this->availability,
            'deviceToken'=>$this->deviceToken,
            'user_id'=>$this->user_id,
            
        ];
    }
}
