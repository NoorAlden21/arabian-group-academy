<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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

    public function All(Request $request): JsonResponse
    {
        $notifications =Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead($id, Request $request): JsonResponse
    {
        $notification = \App\Models\Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }
}
