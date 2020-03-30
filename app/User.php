<?php

namespace App;

use Laravel\Passport\HasApiTokens;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Driver\DriverResource;
use App\Models\General\category;
use Hash;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $permisions =[];
    protected $table ='users';
    protected $fillable = [
        'name', 'email', 'password','UserType'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','pivot','admin','UserType'
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',"Status"=>'boolean'
    ];

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function resturant()
    {
        return $this->hasOne(Resturant::class);
    }
    public function havePermision($name)
    {
        return $this->admin->permisions()->where('name', $name)->first() !=null;
    }


    public function isSuperDashBoardAdmin()
    {
        return $this->admin->role->type->id ==2;
    }

    public function isMiniDashBoardAdmin()
    {
        return $this->admin()->role()->type->id ==1;
    }



    public function getAuthUser()
    {
        return new UserResource($this);
    }

    public function getCurrentDriverProfile()
    {
        return [
            'user'=> new DriverResource($this),
            'categories'=>category::getdriverCategoriesMobile()
        ];
    }

    public function saveAdmin()
    {
        $this->saveUser('admin');

        if ($this->admin) {
            $this->admin()->update([
                'hidden'=>false,
                'role_id' =>request()->role_id,
                'mini_dashboard_id' =>(request()->dashboardId==0) ?request()->mini_dashboard_id:request()->dashboardId
            ]);
        } else {
            $this->admin()->Create([
                'hidden'=>false,
                'role_id' =>request()->role_id,
                'mini_dashboard_id' =>(request()->dashboardId==0) ?request()->mini_dashboard_id:request()->dashboardId
            ]);
        }
    }

    public function saveResturant()
    {
    }


    public function saveDriver()
    {
    }

    public function saveUser($type)
    {
        $this->name=request()->name;
        $this->email=request()->email;
        $this->Status= isset(request()->Status) ? request()->Status:true;
        if (request()->password) {
            $this->password=Hash::make(request()->password);
        }

        $this->UserType=$type;
        $this->save();
    }

    public function resetPassword()
    {
        $newpass =Hash::make("Se@12345");

        $update =  $this->password = $newpass;

        $this->save;
    }
}
