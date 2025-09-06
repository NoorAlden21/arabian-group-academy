<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\DeviceToken;
use App\Models\Notification as ModelsNotification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->toArray();

        // خزّن الإشعار في جدول notifications
        ModelsNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        if (empty($tokens)) {
            return; // المستخدم ما عنده أي توكن → يتخزن بس في DB
        }

        $firebaseNotification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($firebaseNotification)->withData($data);

        foreach ($tokens as $token) {
            $this->messaging->send($message->withChangedTarget('token', $token));
        }
    }


    public function sendToTopic(string $topic, string $title, string $body, array $data = []): void
    {
        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data)
            ->withChangedTarget('topic', $topic);

        $this->messaging->send($message);
    }
}
