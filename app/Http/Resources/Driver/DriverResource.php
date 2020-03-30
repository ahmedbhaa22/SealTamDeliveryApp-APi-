<?php

namespace App\Http\Resources\Driver;

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
            'name'=> $this->name,
            'email'=> $this->email,
            'UserType'=> $this->UserType,
            'rate'=> $this->rate,
            'image'=> $this->driver->image,
            'telephone'=>  $this->driver->telephone,
            'CurrentBalance'=> $this->driver->CurrentBalance,
            'canReceiveOrder'=> $this->driver->canReceiveOrder,
            'availability'=> $this->driver->availability,
            'busy'=> $this->driver->busy,
            'user_id'=> $this->driver->user_id,
            'mini_dashboard_id'=> $this->driver->mini_dashboard_id ?? 0,
            'category_id'=> $this->driver->category_id,
            'category_arabicname'=>$this->driver->category->arabicname ??'',
            'category_english_name'=>$this->driver->category->englishname ??'',
            'needToActivate'=>true,

        ];
    }
}
