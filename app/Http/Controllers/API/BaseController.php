<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendResponse($result, $message, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, $statusCode);
    }


    /**
     * return error response.
     *
     * @param $error
     * @param array $errorMessages
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendError($error, array $errorMessages = [], int $statusCode = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $statusCode);
    }
}
