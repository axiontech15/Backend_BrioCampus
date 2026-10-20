<?php

namespace App\Http\Controllers\V1;

use App\Http\Services\V1\NotificationService;
use App\Http\Requests\V1\NotificationRequest;
use Illuminate\Http\JsonResponse;

class NotificationController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
 
    public function index():JsonResponse
    {
        $notifications = $this->notificationService->getAllNotifications();
        return response()->json($notifications, 200);
    }

   
    public function store(NotificationRequest $request):JsonResponse
    {
        $notification = $this->notificationService->createNotification($request->all());
        return response()->json($notification, 201);
    }

    public function show(int $id):JsonResponse
    {
        $notification = $this->notificationService->getNotificationById($id);
        return response()->json($notification, 200);
    }
  
    public function destroy(int $id):JsonResponse
    {
        $this->notificationService->deleteNotification($id);
        return response()->json(null, 204);
    }
}
