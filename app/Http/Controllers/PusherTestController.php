<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class PusherTestController extends Controller
{
    public function test()
    {
        event(new MessageSent([
            'id' => 1,
            'user' => 'System',
            'message' => 'Test message at ' . now(),
            'time' => now()->format('H:i:s')
        ]));

        return response()->json(['status' => 'Message sent']);
    }
}