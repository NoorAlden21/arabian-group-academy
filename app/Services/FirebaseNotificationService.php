<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\DeviceToken;

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

        if (empty($tokens)) {
            return;
        }

        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($notification)->withData($data);

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
