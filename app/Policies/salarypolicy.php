<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class salarypolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function before($user, $ability)
    {
        if (!$user->isSuperDashBoardAdmin()) {
            return false;
        }
    }

    public function CanCreateDeduction($user)
    {
        return $user->havePermision('Can Create Deduction');
    }

    public function CanDeleteDeduction($user)
    {
        return $user->havePermision('Can Delete Deduction');
    }

    public function CanPaySalary($user)
    {
        return $user->havePermision('Can Pay Salary');
    }
}
