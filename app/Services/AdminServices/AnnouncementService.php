<?php

namespace App\Services\AdminServices;

use App\Models\Customer;
use App\Models\Notification;
use App\Observers\AnnouncementSubject;
use App\Observers\CustomerObserver;

class AnnouncementService
{
    public function broadcast(string $message, array $customerIds = []): void
{
    $subject = new AnnouncementSubject($message);

    // If no IDs given, fallback to all
    $customers = empty($customerIds)
        ? Customer::all()
        : Customer::whereIn('customer_id', $customerIds)->get();

    foreach ($customers as $customer) {
        $subject->addObserver(new CustomerObserver($customer));
    }

    $subject->notifyObserver();
}

}
