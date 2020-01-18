<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\employeeResource;
use DB;

class employee extends BaseModel
{
    public $casts = [
        'salary'=>'double'
        ];
    public static function getListPage()
    {
        return employeeResource::collection(self::all());
    }

    public function getEditPage()
    {
        return new employeeResource($this);
    }
    public function store($request)
    {
        $this->setData($request);
        $this->save();
    }

    public function edit($request)
    {
        $this->setData($request);
        $this->save();
    }

    public function setData($request)
    {
        $this->image = $this->storeFile('employeeImages', 'image');
        $this->contract_image = $this->storeFile('contract_images', 'contract_image');
        $this->name = $request->name;
        $this->birthdate = $request->birthdate;
        $this->phone = $request->phone;
        $this->contract_finish_date = $request->contract_finish_date;
        $this->email = $request->email;
        $this->salary = $request->salary;
        $this->working_hours = $request->working_hours;
    }

    public function paysalary($request)
    {
        if (salary_deduction_raise::isPaid($request)) {
            return 'salary already Paid';
        }
        $raise = salary_deduction_raise::getMonthRaiseSum($request->employee_id, $request->year, $request->month);
        $deduction = salary_deduction_raise::getMonthDeductionSum($request->employee_id, $request->year, $request->month);
        $amount = $this->salary -$deduction +$raise;
        if ($amount < 0) {
            return 'salary less than 0';
        }
        DB::beginTransaction();

        try {
            expense::create([
                'amount'=>$amount,
                'describtion'=>'salary is Paid For Employee Number #'.$request->employee_id,
                'user_id'=>\auth()->user()->id ,
                'type'=>'salary',
                'date'=>$request->year .'-'.$request->month.'-'.'1',
                'employee_id'=>$request->employee_id
                 ]);
            salary_deduction_raise::markasUsed($request);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
