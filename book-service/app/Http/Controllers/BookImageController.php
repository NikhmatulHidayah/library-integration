<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\BookImage;

class BookImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'image' => 'required|image|max:2048',
        ]);

        $image = $request->file('image');
        $imageBase64 = base64_encode(file_get_contents($image));

        $apiKey = 'db7b3539b3aa0fe22d45cdf248e4b2fc';

        $response = Http::asForm()->post("https://api.imgbb.com/1/upload", [
            'key' => $apiKey,
            'image' => $imageBase64,
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to upload image'], 500);
        }

        $imageUrl = $response->json('data.url');

        $bookImage = BookImage::create([
            'book_id' => $request->book_id,
            'image_url' => $imageUrl,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => $bookImage,
        ]);
    }
}
