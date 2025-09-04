<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function __construct(
        private FirebaseNotificationService $firebaseService
    ) {}

    public function createAndSend(int $userId, string $title, string $body, array $data = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }
}
