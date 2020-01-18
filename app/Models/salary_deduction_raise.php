<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\employee;

class salary_deduction_raise extends BaseModel
{
    public function store($request)
    {
        $this->setData($request);
        $this->save();
    }



    public function setData($request)
    {
        $this->amount	 = $request->amount	;
        $this->date = $request->date;
        $this->type = $request->type;
        $this->reason = $request->reason;
        $this->employee_id = $request->employee_id;
    }

    public static function getMonthDeductionSum($employee_id, $year, $month)
    {
        return  Self::where('employee_id', $employee_id)
        ->where('type', 'deduction')
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->sum('amount');
    }
    public static function getMonthRaiseSum($employee_id, $year, $month)
    {
        return  Self::where('employee_id', $employee_id)
        ->where('type', 'raises')
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->sum('amount');
    }

    public static function getList($request)
    {
        return  Self::where('employee_id', $request->employee_id)
        ->where('type', $request->type)
        ->whereYear('date', $request->year)
        ->whereMonth('date', $request->month)
        ->get();
    }

    public static function isPaid($request)
    {
        return  expense::where('employee_id', $request->employee_id)
        ->where('type', 'salary')
        ->whereYear('date', $request->year)
        ->whereMonth('date', $request->month)
        ->first()!=null;
    }
    public static function markasUsed($request)
    {
        return  Self::where('employee_id', $request->employee_id)
        ->whereYear('date', $request->year)
        ->whereMonth('date', $request->month)
        ->update([
            'is_used'=>true
        ]);
    }
    public static function getPayCheckPage($request)
    {
        return [
          'DeductionANdRaise' => self::getList($request),
          'raiseSum'=>self::getMonthRaiseSum($request->employee_id, $request->year, $request->month),
          'DeductionSum'=>self::getMonthDeductionSum($request->employee_id, $request->year, $request->month),
          'salary'=>employee::find($request->employee_id)->salary,

        ];
    }
}
