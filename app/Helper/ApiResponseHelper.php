<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseHelper
{
    public static function returnJSON($data = [], $status = true, $code = JsonResponse::HTTP_OK , $message = 'Data Has Fetched successfully')
    {
        if ($data instanceof JsonResource) {
            return $data->additional([
                'status'  => $status,
                'message' => $message,
            ]);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function returnSuccessMessage($message = 'Request Has Done successfully')
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ], JsonResponse::HTTP_OK);
    }

    public static function returnErrorMessage($message = 'Request Is Invalid', $code = JsonResponse::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }
}
