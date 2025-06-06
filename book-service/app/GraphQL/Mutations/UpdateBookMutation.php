<?php

namespace App\GraphQL\Mutations;

use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class UpdateBookMutation
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
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $book = Book::findOrFail($args['id']);

        $book->update($args['input']);

        return $book;
    }
}
