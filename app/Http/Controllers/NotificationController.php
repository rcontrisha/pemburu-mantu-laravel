<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OneSignal;

class NotificationController extends Controller
{
    public function sendNotification()
    {
        try {
            OneSignal::sendNotificationToAll(
                "Hello from Laravel!",
                "This is a test message sent using OneSignal",
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
