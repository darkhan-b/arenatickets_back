<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait APIResponseTrait {

    public function apiValidationFailed(Request $request, array $rules) {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            return false;
        }
        $errors = implode(' ',$validator->errors()->all());
//        return $this->apiError(422,$errors,[]);
        return $this->apiError(200,$errors,[]);
    }

    public function apiSuccess($data) {
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => null,
            'data' => $data
        ],200, [], JSON_UNESCAPED_UNICODE);
    }

    public function apiError($code, $message = '', $data = []) {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }
}
