<?php

namespace App;

use App\Models\Dashboard\mini_dashboard;
use Illuminate\Database\Eloquent\Model;

use App\Models\Dashboard\roles;
use Illuminate\Database\Eloquent\Builder;

class Admin extends Model
{
    protected $table = 'admins';
    // protected $guarded = [];
    protected $fillable = [
        'user_id',
        'role_id',
        'mini_dashboard_id'
    ];
    protected $hidden = [
        'pivot'
    ];
    public function role()
    {
        return $this->belongsTo('App\Models\Dashboard\roles');
    }


    public function permisions()
    {
        return $this->role->permisions;
    }

    public function mini_dashboard()
    {
        return $this->belongsTo('App\Models\Dashboard\mini_dashboard');
    }
    public function mini_dashboard_admins()
    {
        $querybilder =$this ;
        if (request()->dashboardId!=0) {
            $querybilder = $querybilder->where('mini_dashboard_id', request()->dashboardId);
        }
        return $querybilder->whereHas('role', function (Builder $query) {
            $query->where('type_id', 1);
        })->get();
    }
    public function User()
    {
        return $this->belongsTo('App\User');
    }

    public function dashboards()
    {
        if ($this->role->type_id==2) {
            return mini_dashboard::all();
        } else {
            return $this->mini_dashboard;
        }
    }

    // public function getCreatePage()
    // {
    //     return [
    //         'roles'=>  RolesShortResource::collection(roles::where('type_id', 1)->get()),
    //         'mini_dashboard'=>MiniDashboardShortResource::collection(mini_dashboard::all())
    //     ];
    // }
    // public function getEditPage()
    // {
    //     return [
    //         'roles'=>  RolesShortResource::collection(roles::where('type_id', 1)->get()),
    //         'mini_dashboard'=>MiniDashboardShortResource::collection(mini_dashboard::all()),
    //         'data'=>$this->with('User')
    //     ];
    // }
}
