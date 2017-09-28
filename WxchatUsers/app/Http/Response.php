<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class Response
{
    public static function success($data = null)
    {
        return JsonResponse::create([
            'success' => true,
            'errors'  => [],
            'data'    => $data,
        ]);
    }

    public static function errors(array $errors = [])
    {
        return JsonResponse::create([
            'success' => false,
            'data'    => null,
            'errors'  => $errors,
        ]);
    }
}
