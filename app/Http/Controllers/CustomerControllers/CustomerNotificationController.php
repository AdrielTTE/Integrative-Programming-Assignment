<?php
namespace App\Http\Controllers\CustomerControllers;
use App\Models\Delivery;
use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryService;
use App\Services\CustomerServices\CustomerNotificationService;
use Illuminate\Notifications\Notification;
use App\Services\Api\PackageService;


class CustomerNotificationController extends Controller{

protected PackageService $packageService;
protected DeliveryService $deliveryService;
protected CustomerNotificationService $notificationService;

public function __construct(DeliveryService $deliveryService, PackageService $packageService)
{
    $this->notificationService = new CustomerNotificationService(
        $packageService,
        $deliveryService


    );
}
    public function notification(){

        $notifications = $this->notificationService->getNotifications();
        return view('CustomerViews.Notifications.notification', compact('notifications'));
    }



}
