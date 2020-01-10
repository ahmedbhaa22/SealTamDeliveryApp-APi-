<?php

namespace App\Policies;

use App\User;
use App\Models\Dashboard\roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class basePolicy
{
    use HandlesAuthorization;
    public $modelName;
    /**
     * Determine whether the user can view any roles.
     *
     * @param  \App\User  $user
     * @return mixed
     */

    public function __construct($modelName)
    {
        $this->modelName =$modelName;
    }



    public function viewAny(User $user)
    {
        return $user->havePermision('Can View '.$this->modelName);
    }

    /**
     * Determine whether the user can view the roles.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Dashboard\roles  $roles
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->havePermision('Can View '.$this->modelName);
    }

    /**
     * Determine whether the user can create roles.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->havePermision('Can Create '.$this->modelName);
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
        return $user->havePermision('Can Edit '.$this->modelName);
    }

    public function getEdit(User $user)
    {
        return $user->havePermision('Can Edit '.$this->modelName);
    }
    public function getList(User $user)
    {
        return $user->havePermision('Can View '.$this->modelName);
    }
    public function getCreate(User $user)
    {
        return $user->havePermision('Can Edit '.$this->modelName);
    }
    /**
     * Determine whether the user can delete the roles.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Dashboard\roles  $roles
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->havePermision('Can Edit '.$this->modelName);
    }

    /**
     * Determine whether the user can restore the roles.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Dashboard\roles  $roles
     * @return mixed
     */
    public function restore(User $user)
    {
        return $user->havePermision('Can Edit '.$this->modelName);
    }

    /**
     * Determine whether the user can permanently delete the roles.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Dashboard\roles  $roles
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        return $user->havePermision('Can Edit '.$this->modelName);
    }
}
