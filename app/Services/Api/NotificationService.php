<?php

namespace App\Services\Api;

use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class NotificationService
{
    public function getAll()
    {
        return Notification::orderByDesc('created_at')->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|exists:customers,customer_id',
            'message'     => 'required|string|max:1000',
            'type'        => 'nullable|string|max:50',
            'read_at'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $nextId = $this->generateNextNotificationId();

        $notificationData = array_merge($validator->validated(), [
            'notification_id' => $nextId,
        ]);

        return Notification::create($notificationData);
    }

    public function getById(string $notificationId)
    {
        return Notification::where('notification_id', $notificationId)->firstOrFail();
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Notification::orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $notificationId, array $data)
    {
        $notification = Notification::where('notification_id', $notificationId)->firstOrFail();

        $validator = Validator::make($data, [
            'message'     => 'sometimes|required|string|max:1000',
            'read_at'     => 'nullable|date',
            'type'        => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $notification->update($validator->validated());

        return $notification;
    }

    public function delete(string $notificationId): void
    {
        $notification = Notification::where('notification_id', $notificationId)->firstOrFail();
        $notification->delete();
    }

    public function generateNextNotificationId(): string
    {
        $latest = Notification::orderByDesc('notification_id')->first();

        if (!$latest) {
            return 'N00001';
        }

        $lastId = $latest->notification_id; // e.g., 'N00009'
        $numericPart = intval(substr($lastId, 1)); // Remove the 'N' and convert to int
        $nextNumber = $numericPart + 1;

        return 'N' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function getByCustomerId(string $customerId)
{
    return Notification::where('customer_id', $customerId)
        ->whereNull('read_at') // only unread notifications
        ->orderByDesc('created_at')
        ->get();
}


    public function markAsRead(string $notificationId)
    {
        $notification = Notification::where('notification_id', $notificationId)->firstOrFail();
        $notification->read_at = now();
        $notification->save();

        return $notification;
    }
}
