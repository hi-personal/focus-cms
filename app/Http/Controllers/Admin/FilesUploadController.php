<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class FilesUploadController extends Controller
{
    public function index()
    {
        return view('admin.files-upload');
    }

    public function handleUpload(Request $request): JsonResponse
    {
        try {
            // Ellenőrizzük, hogy van-e feltöltött fájl
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nincs fájl a kérésben!'
                ], 400);
            }

            // Validáció
            $request->validate([
                'file' => 'required|file|max:102400' // 10MB limit
            ]);

            // Fájl mentése a "public/uploads" mappába
            $path = $request->file('file')->store('uploads', 'public');

            return response()->json([
                'success' => true,
                'message' => 'Fájl sikeresen feltöltve!',
                'path' => Storage::url($path) // Teljes elérési út visszaadása
            ]);

         } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba: ' . $e->getMessage(),
            ], 500);
        }
    }
}