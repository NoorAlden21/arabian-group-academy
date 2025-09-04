<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationController extends Controller
{

    public function index(): JsonResponse
    {
        $notifications = auth()->user()->notifications()->latest()->get();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function send(Request $request)
    {
        $messaging = Firebase::messaging();

        $deviceToken = $request->input('token');
        $title = $request->input('title', 'Test Notification');
        $body = $request->input('body', 'Hello from Laravel & Firebase!');

        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        try {
            $messaging->send($message);
            return response()->json(['status' => 'success', 'message' => 'Notification sent!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
