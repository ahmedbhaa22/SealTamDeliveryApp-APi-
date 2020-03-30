<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            "email"=>$this->email,
            "status"=>$this->Status,
            "rate"=>$this->when($this->UserType!='admin', $this->rate),
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at,
            'access_token'=>$this->when($this->access_token, $this->access_token),
            'refresh_token'=>$this->when($this->refresh_token, $this->refresh_token),
            'dashboard_ype'=>$this->when($this->UserType=='admin', $this->admin->role->type),
            "miniDashboards"=>$this->when($this->UserType=='admin', $this->admin->role->type_id==2 ? MiniDashboardResource::Collection($this->admin->dashboards()):new MiniDashboardResource($this->admin->dashboards())),
            "permision"=>$this->when($this->UserType=='admin', function () {
                return array_map(
                    function ($e) {
                        return $e['name'];
                    },
                    $this->admin->permisions()->toArray()
                );
            })
        ];
    }
}
