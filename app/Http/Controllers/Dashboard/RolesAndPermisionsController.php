<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use App\Models\Dashboard\roles;
use App\Http\Resources\rolesResource;

class RolesAndPermisionsController extends BaseController
{
    public function getRolesListPage()
    {
        return $this->Response(true, rolesResource::collection(roles::all()));
    }

    public function getRolesCreatePage()
    {
        return $this->Response(true, (new roles())->createPageVM(), null);
    }

    public function getRolesEditPage($role_id)
    {
        $role = roles::find($role_id);
        if ($role == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }

        return $this->Response(true, $role->EditePageVM());
    }

    public function Create(Request $request)
    {
        return $this->Response(true, (new roles())->Create($request, $request->type_id), "messages.Globale.Saved");
    }

    public function Edit(Request $request, $role_id)
    {
        $role = roles::find($role_id);
        if ($role == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        return $this->Response(true, $role->Edit($request), "messages.Globale.Saved");
    }
}
