<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Dashboard\mini_dashboard;
use DB;

class codes extends BaseModel
{
    public function store($request)
    {
        $this->setData($request);
        $this->save();
    }

    public function setData($request)
    {
        $this->code = $request->code;
        $this->amount = $request->amount;
        $this->type	 = $request->type;
        $this->numOfDays = $request->numOfDays;
    }

    public function useCodeForMIniDashboard()
    {
        $request = request();
        if ($this->type!='mini_dashboard_codes' || $this->used) {
            return 'not-valid';
        }
        DB::beginTransaction();
        try {
            $mini_dashboard = mini_dashboard::find($request->dashboardId);
            $request->request->add(['days' =>$this->numOfDays]);
            $request->request->add(['amount' =>$this->amount]);
            $request->request->add(['mini_dashboard_id' =>$this->dashboardId]);
            $request->request->add(['date' =>date('Y-m-d')]);
            $request->request->add(['dashboardId' =>$this->dashboardId]);

            $mini_dashboard->reactivate($request);
            $this->used = true;
            $this->save();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }

    public static function getListPage()
    {
        return self::orderBy('created_at', 'DESC')->get();
    }
}
