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
            'image_url' => 'required|url',
        ]);

        $bookId = $request->book_id;
        $imageUrl = $request->image_url;

        $bookImage = BookImage::create([
            'book_id' => $bookId,
            'image_url' => $imageUrl,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => $bookImage,
        ]);
    }
}
