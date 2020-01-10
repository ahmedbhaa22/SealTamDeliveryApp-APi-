<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class driverPolicy extends SystemMainModlesPolicy
{
    public function __construct()
    {
        parent::__construct('drivers');
    }

    public function resetBalanace($user)
    {
        return $user->havePermision('Can Reset Balance');
    }
}
