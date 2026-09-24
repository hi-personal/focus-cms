<?php

namespace App\Http\Controllers\Api; // <- Ennek egyeznie kell a routes/api.php use-ával!

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PwaConfigController extends Controller
{
    public function getConfig(): JsonResponse
    {
        return response()->json([
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
            'measurementId' => config('services.firebase.measurement_id'),
            'vapidKey' => config('services.firebase.vapid_key'),
        ]);
    }
}