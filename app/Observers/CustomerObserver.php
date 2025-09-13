<?php

namespace App\Observers;

use App\Observers\Observer;
use App\Observers\Subject;
use App\Notifications\DeliveryStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;


class CustomerObserver implements Observer
{
    public function update(Subject $subject)
    {
        // Make sure we only react to Delivery subjects
        if ($subject instanceof \App\Models\Delivery) {
            $customer = $subject->customer; // assumes Delivery has a customer() relation

            if ($customer) {
                Notification::send(
                    $customer,
                    new DeliveryStatusUpdatedNotification($subject)
                );
            }
        }
    }
}
