<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class employeeResource extends JsonResource
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
            'id'=>$this->id,
            "name"=>$this->name,
            "birthdate"=>$this->birthdate,
            "image"=>\env('APP_URL'). '/storage/'.$this->image,
            "phone"=>$this->phone,
            "contract_image"=>\env('APP_URL'). '/storage/'.$this->contract_image,
            "contract_finish_date"=>$this->contract_finish_date,
            "email"=>$this->email,
            "salary"=>$this->salary,
            "working_hours"=>$this->working_hours,

        ];
    }
}
