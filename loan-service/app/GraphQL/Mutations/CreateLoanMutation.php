<?php

namespace App\GraphQL\Mutations;

use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateLoanMutation
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
            'loan_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
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

        $loan = Loan::create([
            'user_id' => $userId,
            'book_id' => $input['book_id'],
            'loan_date' => $input['loan_date'],
            'return_date' => $input['return_date'] ?? null,
            'status' => 'borrowed',
        ]);

        return $loan;
    }
}
