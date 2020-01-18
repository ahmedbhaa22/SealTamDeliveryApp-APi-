<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Shared\BaseController;
use App\Models\employee;

class EmployeesController extends BaseController
{
    public function GetListPage()
    {
        return $this->Response(true, employee::getListPage());
    }

    public function store(Request $request)
    {
        (new employee())->store($request);

        return $this->Response(true, null);
    }

    public function edit(Request $request, $id)
    {
        $employee = employee::find($id);
        if ($employee == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }

        $employee->edit($request);

        return $this->Response(true, null);
    }

    public function getEditPage($id)
    {
        $employee = employee::find($id);
        if ($employee == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        return $this->Response(true, $employee->getEditPage());
    }

    public function paysalary(Request $request)
    {
        $employee = employee::find($request->employee_id);
        if ($employee == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }
        $result = $employee->paysalary($request);
        if ($result =='salary already Paid') {
            return $this->Response(false, null, "messages.Globale.salaryisPaid");
        }
        if ($result =='salary less than 0') {
            return $this->Response(false, null, "messages.Globale.salaryiszero");
        }
        return $this->Response(true, null);
    }
}
