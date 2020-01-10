<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class categoryPolicy extends baseSuperDashBoardPolicy
{
    public function __construct()
    {
        parent::__construct('category');
    }
}
