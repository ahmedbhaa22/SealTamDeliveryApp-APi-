<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class driverStatusResource extends JsonResource
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
            'lat'=>$this->lat,
            'id'=>$this->user_id,
            'busy'=>$this->busy,
            'availability'=>$this->availability,
        ];
    }
}
