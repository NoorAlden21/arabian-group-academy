<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class TestNotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function send(): JsonResponse
    {
        $this->notificationService->createAndSend(
            userId: 1, // عدل ID المستخدم الذي عنده FCM Token
            title: 'Homework Created',
            body: 'A new homework has been added for your class.',
            data: ['type' => 'homework', 'id' => 55]
        );

        return response()->json([
            'message' => 'Notification sent successfully'
        ]);
    }
}
