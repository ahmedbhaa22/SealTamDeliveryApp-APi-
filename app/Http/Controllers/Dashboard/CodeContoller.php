<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use App\Models\General\codes;

class CodeContoller extends BaseController
{
    public $validationRule=
    [
        'code'	=>'required|string|unique:codes,code',
    ];


    public function GetListPage()
    {
        return $this->Response(true, codes::getListPage());
    }

    public function store(Request $request)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        (new codes())->store($request);

        return $this->Response(true, null);
    }

    public function useForMiniDashboard(Request $request)
    {
        $code = codes::lockForUpdate()->where('code', $request->code)->first();
        if ($code == null) {
            return $this->Response(false, null, "messages.Globale.InvalidCode");
        }

        $result = $code->useCodeForMIniDashboard();
        if ($result =='not-valid') {
            return $this->Response(false, null, "messages.Globale.InvalidCode");
        }
        return $this->Response(true, null);
    }

    public function delete($id)
    {
        $code = codes::find($id);
        if ($code == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        if ($code->used) {
            return $this->Response(false, null, "messages.Globale.used");
        }
        $code->delete();
        return $this->Response(true, null);
    }
}
