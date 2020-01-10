<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MiniDashboardResource;
use App\Models\Dashboard\mini_dashboard;
use App\Http\Controllers\Shared\BaseController;
use Validator;

class MiniDashBoardController extends BaseController
{
    public $validationRule=
                [
                    'name'			=>'required|string',
                    'monthly_cost'		=>'required|numeric|min:0',
                    'earning_ratio'		=>'required|numeric|min:0|max:100',
                    'number_of_drivers'		=>'required|numeric|min:0',
                    'active'		=>'boolean',
                    'days_left'		=>'required|numeric|min:0'
                ];


    public function GetListPage()
    {
        return $this->Response(true, (new mini_dashboard())->getListPage());
    }

    public function store(Request $request)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        (new mini_dashboard())->Create($request);

        return $this->Response(true, null);
    }

    public function edit(Request $request, $id)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        $mini_dashboard = mini_dashboard::find($id);
        if ($mini_dashboard == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }

        $mini_dashboard->Edit($request);

        return $this->Response(true, null);
    }

    public function getEditPage($id)
    {
        return $this->Response(true, new MiniDashboardResource(mini_dashboard::find($id)));
    }
}
