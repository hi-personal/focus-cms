<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index()
    {
        // Példa: Képek listázása a 'public/images' mappából
        $images = Storage::disk('public')->files('images');

        $imageUrls = array_map(function ($image) {
            return asset('storage/' . $image);
        }, $images);

        return response()->json($imageUrls);
    }
}