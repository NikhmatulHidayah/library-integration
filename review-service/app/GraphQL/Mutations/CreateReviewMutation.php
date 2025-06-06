<?php

namespace App\GraphQL\Mutations;

use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateReviewMutation
{
    public function __invoke($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('id');
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: " . $e->getMessage());
        }

        $input = $args['input'];

        $validator = Validator::make($input, [
            'book_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        // Validasi book_id dengan query GraphQL eksternal
        $graphqlQuery = <<<'GRAPHQL'
        {
            books {
                id
            }
        }
        GRAPHQL;

        $response = Http::post('http://172.19.0.16:8082/graphql', [
            'query' => $graphqlQuery
        ]);

        if ($response->failed()) {
            throw new \Exception("Gagal menghubungi service buku.");
        }

        $bookIds = collect($response->json('data.books'))->pluck('id')->toArray();

        if (!in_array($input['book_id'], $bookIds)) {
            throw new \Exception("Buku dengan ID {$input['book_id']} tidak ditemukan.");
        }

        return Review::create([
            'book_id' => $input['book_id'],
            'user_id' => $userId,
            'rating' => $input['rating'],
            'comment' => $input['comment'] ?? null,
        ]);
    }
}
