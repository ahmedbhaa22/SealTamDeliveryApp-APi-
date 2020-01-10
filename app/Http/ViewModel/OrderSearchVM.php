<?php

namespace App\Http\ViewModel;
use Illuminate\Database\Eloquent\Model;
use App;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

  class OrderSearchVM 
{
   public  $driver_id;
   public  $resturant_id;
   public  $crated_at;// 'today','yesterday','month',year,specificdata
   public  $chosen_date;
   public  $status;
    public $queryBuilder ;

   
   public function __construct (Request $request) {
       Carbon::setWeekStartsAt(Carbon::FRIDAY);
       Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $this->driver_id =  $request->driver_id;
        $this->resturant_id =  $request->resturant_id;
        $this->crated_at =  $request->crated_at;
        $this->chosen_date =  $request->chosen_date;
        $this->status =  $request->status;
        $this->queryBuilder= DB::table('orders')
                              ->leftjoin('users as d', 'd.id', '=', 'orders.driver_id')
                              ->leftjoin('users as  r', 'r.id', '=', 'orders.resturant_id');
        $this->BuildQueryBuilder();
    }
    
     
    public function BuildQueryBuilder(){
        $this->driver_id_filter();
        $this->resturant_id_filter();
        $this->status_filter();
        $this->date_filter();
    }
    
    private function driver_id_filter(){
        if($this->driver_id!= null){
            $this->queryBuilder = $this->queryBuilder->where('orders.driver_id',$this->driver_id);
        }
            
    }
           
    private function resturant_id_filter(){
        if($this->resturant_id != null){
            $this->queryBuilder =  $this->queryBuilder->where('orders.resturant_id',$this->resturant_id);
        }
            
    }
   private function status_filter(){
        if($this->status != null){
              $this->queryBuilder = $this->queryBuilder->where('orders.status',$this->status);
        }
            
    } 
    
    private function date_filter(){
        if($this->crated_at == 'today'){
           $this->queryBuilder =   $this->queryBuilder->whereDate('orders.created_at', Carbon::today());
        }
     else   if($this->crated_at == 'yesterday'){
            $yesterday = date("Y-m-d", strtotime( '-1 days' ) );

            $this->queryBuilder =  $this->queryBuilder->whereDate('orders.created_at', $yesterday);
        }
       else  if($this->crated_at == 'month'){
          $this->queryBuilder =    $this->queryBuilder->whereMonth('orders.created_at',  Carbon::now()->month)->whereYear('orders.created_at',  Carbon::now()->year);
        }
       else if($this->crated_at == 'weak'){
       $this->queryBuilder =       $this->queryBuilder->whereBetween('orders.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

        }
    
        else if($this->crated_at == 'year'){
           $this->queryBuilder =    $this->queryBuilder->whereYear('orders.created_at',  Carbon::now()->year);

        }
        else if($this->crated_at == 'date'){
             $this->queryBuilder =  $this->queryBuilder->whereDate('orders.created_at', $this->chosen_date);

        }
    } 
}
