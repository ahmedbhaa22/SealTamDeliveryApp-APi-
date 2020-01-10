<?php

namespace App\Policies;

use App\User;
use App\Models\Dashboard\roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class mini_dashboardPolicy extends baseSuperDashBoardPolicy
{
    public function __construct()
    {
        parent::__construct('mini_dashboard');
    }
}
