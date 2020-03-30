<?php
namespace App\Helpers;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use App\Driver;

class FireBaseHelper
{
    private static $instance;

    private function __construct()
    {
        $this->serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/sealteamdeliveryapp-firebase-adminsdk-yra65-b8ba7856bd.json');
        $this->firebase = (new Factory)->withServiceAccount($this->serviceAccount)->withDatabaseUri('https://sealteamdeliveryapp.firebaseio.com')->create();
        $this->database = $this->firebase->getDatabase();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function deleteOrder($order)
    {
        $this->database
                ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                ->set(null);
    }
    public function addOrder($order)
    {
        if ($order->driver_id) {
            $driver = $order->Assigneddriver()->with('userInfo')->first();
            $order['DriverName'] =$driver->userInfo->name;
            $order['DriverPhone'] =$driver->telephone;
            $order['Driverlat'] =$driver->lat;
            $order['Driverlng'] =$driver->lng;
            $order['DriverRate'] =$driver->userInfo->rate;
            $order['DriverImage'] =$driver->image;
        }
        $this->database
                ->getReference('Orders/'.$order['resturant_id'].'/'.$order['id'])
                ->set($order);
    }
}
