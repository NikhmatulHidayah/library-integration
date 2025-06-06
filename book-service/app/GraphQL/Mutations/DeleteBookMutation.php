<?php

namespace App\GraphQL\Mutations;

use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class DeleteBookMutation
{
    public function __invoke($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('sub'); 
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: ".$e->getMessage());
        }

        $book = Book::find($args['id']);

        if (!$book) {
            throw new \Exception("Book not found.");
        }

        $book->images()->delete();

        return $book->delete();
    }
}
