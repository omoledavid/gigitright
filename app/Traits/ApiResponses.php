<?php

namespace App\Traits;

trait ApiResponses {
    public function ok($message, $data)
    {
        return $this->success($message, $data);
    }
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    protected function error($message, $status = 400)
    {
        return response()->json([
            'message' => $message,
            'status' => $status
        ], $status);
    }
}
