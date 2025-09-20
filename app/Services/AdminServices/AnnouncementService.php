<?php

namespace App\Services\AdminServices;

use App\Models\Customer;
use App\Models\Notification;
use App\Observers\AnnouncementSubject;
use App\Observers\CustomerObserver;

class AnnouncementService
{
    public function broadcast(string $message): void
    {
        $subject = new AnnouncementSubject($message);

        // You can filter customers here if needed (e.g., only active ones)
        $customers = Customer::all();

        foreach ($customers as $customer) {
            $subject->addObserver(new CustomerObserver($customer));
        }

        $subject->notifyObserver();
    }
}
