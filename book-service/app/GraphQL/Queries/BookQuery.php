<?php

namespace App\GraphQL\Queries;

use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class BookQuery
{
    public function allBooks($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub'); 
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: ".$e->getMessage());
        }

        return Book::with('images')->get();
    }

    public function searchBooks($_, array $args)
    {
        $keyword = $args['keyword'];
    
        return \App\Models\Book::with('images')
            ->where('title', 'like', "%{$keyword}%")
            ->orWhere('author', 'like', "%{$keyword}%")
            ->orWhere('category', 'like', "%{$keyword}%")
            ->get();
    }
}
