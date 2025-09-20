<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = $this->notificationService->getAll();
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $notification = $this->notificationService->create($request->all());
        return response()->json($notification, Response::HTTP_CREATED);
    }

    public function show(string $notification_id)
    {
        $notification = $this->notificationService->getById($notification_id);
        return response()->json($notification);
    }

    public function getByCustomerId(string $customer_id)
    {
        $notifications = $this->notificationService->getByCustomerId($customer_id);
        return response()->json($notifications);
    }


    public function update(Request $request, string $notification_id)
    {
        $notification = $this->notificationService->update($notification_id, $request->all());
        return response()->json($notification);
    }


    public function destroy(string $notification_id)
    {
        $this->notificationService->delete($notification_id);
        return response()->json(['message' => 'Notification deleted.'], Response::HTTP_OK);
    }

    public function paginated(int $pageNo)
    {
        $paginated = $this->notificationService->getPaginated($pageNo);
        return response()->json($paginated);
    }


public function nextId()
{
    $nextId = $this->notificationService->generateNextNotificationId();
    return response()->json(['next_notification_id' => $nextId]);
}

public function markAsRead(string $notification_id)
{
    $notification = $this->notificationService->markAsRead($notification_id);
    return response()->json($notification);

}
}
