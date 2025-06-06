<?php

namespace App\GraphQL\Mutations;

use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class CreateBookMutation
{
    public function __invoke($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub'); 
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: ".$e->getMessage());
        }

        $validator = Validator::make($args['input'], [
            'title' => 'required|string',
            'author' => 'required|string',
            'category' => 'nullable|string',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $book = Book::create([
            'title' => $args['input']['title'],
            'author' => $args['input']['author'],
            'category' => $args['input']['category'] ?? null,
            'stock' => $args['input']['stock'],
        ]);

        return $book;
    }
}
