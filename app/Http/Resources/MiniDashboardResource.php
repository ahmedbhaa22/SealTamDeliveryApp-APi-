<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MiniDashboardResource extends JsonResource
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
            "active"=>$this->when(request()->is('*/GetEditPage*'), $this->active),
            "status"=>$this->when(request()->is('*/GetListPage*'), $this->active ? trans("messages.Globale.Active"): trans("messages.Globale.NotActive")),
            "monthly_cost"=>$this->when(request()->is('*Page*'), $this->monthly_cost),
            "current_credit"=>$this->when(request()->is('*Page*'), $this->current_credit),
            "number_of_drivers"=>$this->when(request()->is('*Page*'), $this->number_of_drivers),
            "days_left"=>$this->when(request()->is('*Page*'), $this->days_left),
            "earning_ratio"=>$this->when(request()->is('*Page*'), $this->number_of_drivers),

        ];
    }
}
