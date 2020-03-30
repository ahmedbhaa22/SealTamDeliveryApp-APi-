<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Resources\Json\JsonResource;

class categoryResource extends JsonResource
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
         "id"=>$this->id,
         "arabicname"=>$this->arabicname,
         "englishname"=>$this->englishname,
         'fullname'=>app()->getLocale() == 'en'? $this->id .'-' .$this->englishname :$this->id .'-' .$this->arabicname,
         "type"=>$this->type,
         "icon"=>$this->icon
     ];
    }
}
