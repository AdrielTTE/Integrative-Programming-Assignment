<?php

use App\Models\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Notification;


class CustomerNotificationController extends Controller{
    public function notification(){

        return view('customer.notification');
    }

 

}
