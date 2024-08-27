<?php

namespace App\Traits;

trait APIResponseTrait
{
    public function success_response($message, $data=null){
        return response([
            'status' => "success",
            'message' => $message,
            'data' => $data
        ], 200);
    }

    public function failed_response($message, $status_code=500){
        return response([
            'status' => 'failed',
            'message' => $message
        ], $status_code);
    }
}