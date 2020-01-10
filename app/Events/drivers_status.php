<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use DB;

class drivers_status implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'drivers_status';

    public $driverid;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($driverid)
    {
        $this->driverid = $driverid;
    }

    public function broadcastAs()
    {
        return 'statusUpdate';
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('statusUpdate');
    }

    public function broadcastWith()
    {
        $driver = DB::table('users')
        ->join('drivers', 'users.id', '=', 'drivers.user_id')
        ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
        ->where('users.UserType', 'driver')->where('drivers.user_id', $this->driverid)
        ->first();
        $this->Driver =$driver;
        return ['Driver' => $this->Driver];
    }
}
