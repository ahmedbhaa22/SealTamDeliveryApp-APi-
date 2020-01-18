<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class codesPolicy extends baseSuperDashBoardPolicy
{
    public function __construct()
    {
        parent::__construct('codes');
    }

    public function delete(User $user)
    {
        return $user->havePermision('Can Delete '.$this->modelName);
    }
}
