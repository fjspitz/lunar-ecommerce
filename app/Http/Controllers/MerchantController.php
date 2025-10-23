<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index()
    {
        //
    }

    public function new(Request $request): JsonResponse
    {
        //

        return response()->json();
    }
}
