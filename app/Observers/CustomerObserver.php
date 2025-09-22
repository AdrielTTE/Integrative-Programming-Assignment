<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Customer;
use Illuminate\Support\Facades\Http;

class CustomerObserver implements Observer
{
    protected $customer;
    protected string $baseUrl;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
         $this->baseUrl = config('services.api.base_url', 'http://localhost:8001/api');
    }

    public function update(Subject $subject)
    {
        $response = Http::get("{$this->baseUrl}/notifications/nextId");

    if ($response->failed()) {
        return 0;
    }

    $nextId = $response->json('next_notification_id');

        if ($subject instanceof AnnouncementSubject) {
            Notification::create([
                'notification_id' => $nextId,
                'customer_id' => $this->customer->customer_id,
                'message' => $subject->getMessage(),
            ]);
        }

         if ($subject instanceof PackageSubject) {
        $package = $subject->getPackage();

        if ($package->wasChanged('package_status')) {
            Notification::create([
                'notification_id' => $nextId,
                'customer_id' => $this->customer->customer_id,
                'message' => ('Your package '. $package->package_id. ' status was updated to: ' ). $package->package_status,
            ]);
        }
    }

    }

    public function forceUpdate(Subject $subject)
{
    $response = Http::get("{$this->baseUrl}/notifications/nextId");

    if ($response->failed()) {
        return 0;
    }

    $nextId = $response->json('next_notification_id');

    if ($subject instanceof PackageSubject) {
        $package = $subject->getPackage();

        Notification::create([
            'notification_id' => $nextId,
            'customer_id' => $this->customer->customer_id,
            'message' => 'Your package ' . $package->package_id . ' status was updated to: ' . $package->package_status,
        ]);
    }
}

}

