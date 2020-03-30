<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemMainModlesPolicy extends basePolicy
{
    public function __construct($modelName)
    {
        parent::__construct($modelName);
    }

    public function create(User $user)
    {
        if (!((request()->dashboardId==0
        || (request()->mini_dashboard ==request()->dashboardId)
        &&$user->havePermision('Can Create '.$this->modelName)))
    ) {
            return false;
        }


        return true;
    }

    /**
     * Determine whether the user can update the roles.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Dashboard\roles  $roles
     * @return mixed
     */
    public function update(User $user)
    {
        if (!((request()->dashboardId==0
        || (request()->mini_dashboard == request()->dashboardId)
            &&$user->havePermision('Can Edit '.$this->modelName)))
    ) {
            return false;
        }
        return true;
    }

    public function resetPassword(User $user)
    {
        return $user->havePermision('Can Change password');
    }
}
