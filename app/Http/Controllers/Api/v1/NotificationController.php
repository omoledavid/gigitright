<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\NotificationResources;
use App\Models\Notification;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return $this->ok('success', NotificationResources::collection(auth()->user()->notifications));
    }
    public function read($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($id);
        $notification->markAsRead();
        return $this->ok('success', new NotificationResources($notification));
    }
    public function readAll()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->where('is_read', 0)->get();
        if($notifications->isEmpty()){
            return $this->error('No unread notifications found', 404);
        }
        foreach($notifications as $notification){
            $notification->markAsRead();
        }
        return $this->ok('success', NotificationResources::collection($notifications));
    }
}
