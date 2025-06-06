<?php

namespace App\GraphQL\Mutations;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateReservationMutation
{
    public function __invoke($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('id'); // atau sub tergantung token Anda
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: " . $e->getMessage());
        }

        $input = $args['input'];
        
        $validator = Validator::make($input, [
            'book_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'expire_date' => 'nullable|date|after_or_equal:reservation_date',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

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

        $reservation = Reservation::create([
            'user_id' => $userId,
            'book_id' => $input['book_id'],
            'reservation_date' => $input['reservation_date'],
            'status' => 'pending',
            'expire_date' => $input['expire_date'] ?? null,
        ]);

        return $reservation;
    }
}
