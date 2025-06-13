<?php

namespace App\GraphQL\Mutations;

use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class UpdateLoanStatusMutation
{
    public function __invoke($_, array $args)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $userId = $payload->get('id');
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: " . $e->getMessage());
        }

        $loanId = $args['id'];
        $status = $args['status'];

        $loan = Loan::findOrFail($loanId);

        $bookId = $loan->book_id;

        $graphqlQuery = <<<'GRAPHQL'
        {
            books {
                id
            }
        }
        GRAPHQL;

        $response = Http::post('http://172.19.0.7:8082/graphql', [
            'query' => $graphqlQuery
        ]);

        if ($response->failed()) {
            throw new \Exception("Gagal menghubungi service buku.");
        }

        $bookIds = collect($response->json('data.books'))->pluck('id')->toArray();

        if (!in_array($bookId, $bookIds)) {
            throw new \Exception("Buku dengan ID {$bookId} tidak ditemukan.");
        }

        $loan->status = $status;
        $loan->save();

        return $loan;
    }
}
