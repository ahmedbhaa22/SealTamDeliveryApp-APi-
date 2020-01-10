<?php

namespace App\Http\Resources;

use App\Models\Dashboard\roles;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RolesShortResource;
use App\Http\Resources\MiniDashboardShortResource;
use App\Models\Dashboard\mini_dashboard;

class adminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
        'id'=>$this->when(request()->is('*/GetEditPage*') ||request()->is('*/GetListPage*'), $this->user_id),
        "name"=>$this->when(request()->is('*/GetEditPage*') ||request()->is('*/GetListPage*'), ($this->User)?$this->User->name:null),
        "email"=>$this->when(request()->is('*/GetEditPage*') ||request()->is('*/GetListPage*'), ($this->User)?$this->User->email:null),
        "status"=>$this->when(request()->is('*/GetListPage*'), ($this->User)? $this->User->Status ? trans("messages.Globale.Active"): trans("messages.Globale.NotActive"):''),
        "Status"=>$this->when(request()->is('*/GetEditPage*'), ($this->User)?$this->User->Status:1),
        "role"=>$this->when(request()->is('*/GetListPage*'), ($this->role)?$this->role->name:''),
        "role_id"=>$this->when(request()->is('*/GetEditPage*'), ($this->role)?$this->role_id:''),
        "mini_dashboard"=>$this->when(request()->is('*/GetListPage*'), ($this->mini_dashboard) ? $this->mini_dashboard->name : 'unKnown'),
        "mini_dashboard_id"=>$this->when(request()->is('*/GetEditPage*'), $this->mini_dashboard_id),
        'roles'=>  $this->when(request()->is('*/GetEditPage*') ||request()->is('*/GetCreatePage*'), RolesShortResource::collection(roles::where('type_id', 1)->get())),
        'mini_dashboards'=> $this->when(request()->is('*/GetEditPage*') ||request()->is('*/GetCreatePage*'), MiniDashboardShortResource::collection((new mini_dashboard())->getAuthorizedOnly())),
        ];
    }
}
