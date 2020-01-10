<?php

namespace App\Observers;

use App\Driver;
use  App\Events\drivers_status;

class DriversObserver
{
    /**
     * Handle the driver "created" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function created(Driver $driver)
    {
        //
    }

    /**
     * Handle the driver "updated" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function updated(Driver $driver)
    {
        $old =   $driver->getOriginal();
        print_r($old);
        event(new drivers_status($request->driver_id));
        if ($old->busy != $driver->busy) {
        }
    }

    /**
     * Handle the driver "deleted" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function deleted(Driver $driver)
    {
        //
    }

    /**
     * Handle the driver "restored" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function restored(Driver $driver)
    {
        //
    }

    /**
     * Handle the driver "force deleted" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function forceDeleted(Driver $driver)
    {
        //
    }
}
