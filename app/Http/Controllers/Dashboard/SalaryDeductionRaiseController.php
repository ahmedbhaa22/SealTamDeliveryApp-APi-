<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Shared\BaseController;
use App\Models\employee;
use App\Models\salary_deduction_raise;

class SalaryDeductionRaiseController extends BaseController
{
    public $validationRule=
    [
        'employee_id'			=>'required|exists:employees,id',
        'amount'		=>'required|numeric|min:1',
        'date'=>'required|date:today'
    ];

    public function create(Request $request)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }


        (new salary_deduction_raise())->store($request);

        return $this->Response(true, null);
    }

    public function getPayCheckPage(Request $request)
    {
        return $this->Response(true, salary_deduction_raise::getPayCheckPage($request));
    }

    public function delete($id)
    {
        $salary_deduction_raise= salary_deduction_raise::find($id);
        if ($salary_deduction_raise ==null ||$salary_deduction_raise->is_used) {
            return $this->Response(false, null, "messages.Globale.salaryisPaid");
        }
        $salary_deduction_raise->delete();
        return $this->Response(true, null);
    }
}
