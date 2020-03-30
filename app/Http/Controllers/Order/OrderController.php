<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use App\Helpers\HelperFunctions;
use App\Helpers\FCM;
use App\Jobs\checkIfOrderDone;

use App\Order;
use DB;

class OrderController extends BaseController
{
    public function CreateOrder(Request $request)
    {
        $resturant = auth()->user()->resturant;
        $this->setOrderValidator($resturant);
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        DB::beginTransaction();
        try {
            $orderDriver= $resturant->getNearstDriverFornewOrder();
            if (count($orderDriver) ==0) {
                return $this->Response(false, null, trans('messages.NoDriverAvailAbleToAcceptThisRequest'));
            }
            $order =new Order();
            $order->create_new_order($resturant, $orderDriver);


            $id = $this->dispatch((new checkIfOrderDone($order))->onQueue('firebase')->delay(now()->addSeconds(70)));
            $order->JobId = $id;
            $order->save();
            DB::commit();
            return $this->Response(true, null);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function setOrderValidator($resturant)
    {
        if ($resturant->typ=='Normal') {
            $this->validationRule =[
                'orderCost'=>'required|numeric|max:500000',
                'customerPhone'=>'required',
                'customerName'=>'required',
                'OrderNumber'=>'sometimes|nullable',
                'orderDest'=>'required',
                'expectedDeliveryCost'=>'required|numeric|max:500000',
            ];
        } elseif ($resturant->typ=='fixedPrice') {
            $this->validationRule =[
                'OrderNumber'=>'sometimes|nullable'
            ];
        }
    }

    public function ResponseFixedPrice(Request $request)
    {
        DB::beginTransaction();
        try {
            $order =Order::lockForUpdate()->find($request->order_id);
            if ($order == null) {
                return $this->Response(false, null, "messages.Globale.InvalidId");
            }
            if ($order->status!='0' || $order->driver_id) {
                return $this->Response(true, null);
            }


            if ($request->status=='1') {
                $order->AcceptForFixed();
            } else {
                $order->refuse();
            }
            DB::commit();
            return $this->Response(true, null);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
