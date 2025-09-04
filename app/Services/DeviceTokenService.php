<?php
namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class DeviceTokenService
{
    private Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function storeToken(User $user, string $token): void
    {
        DeviceToken::where('token', $token)->delete();
        $user->deviceTokens()->create(['token' => $token]);
    }

    public function sendNotification(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->deviceTokens->pluck('token')->toArray();
        if (empty($tokens)) {
            Log::info("No device tokens found for user ID: {$user->id}");
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        try {
            $this->messaging->sendMulticast($message, $tokens);
            Log::info("Notification sent to user ID: {$user->id} successfully.");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to user ID {$user->id}: " . $e->getMessage());
        }
    }
}
