<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class adminPolicy extends SystemMainModlesPolicy
{
    public function __construct()
    {
        parent::__construct('admins');
    }
}
