<?php

namespace App\Policies;

use App\User;
use App\Models\Dashboard\roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class baseMiniDashBoardPolicy extends basePolicy
{
    public function __construct($modelName)
    {
        parent::__construct($modelName);
    }
}
