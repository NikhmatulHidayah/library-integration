<?php

namespace App\GraphQL\Mutations;

use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateLoanMutation
{
    public function __invoke($_, array $args, GraphQLContext $context)
    {
        $token = $context->request()->bearerToken();

        if (!$token) {
            throw new \Exception("Unauthorized: Token not found.");
        }

        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();
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
        query {
          getBookById(id: "%s") {
            id
            title
            author
            category
            stock
          }
        }
        GRAPHQL;

        $response = Http::post('http://172.19.0.7:8082/graphql', [
            'query' => sprintf($graphqlQuery, $input['book_id'])
        ]);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch book details.");
        }

        $book = $response->json('data.getBookById');

        if (!$book) {
            throw new \Exception("Book with ID {$input['book_id']} not found.");
        }

        if ($book['stock'] <= 0) {
            throw new \Exception("Book is out of stock.");
        }

        $newStock = $book['stock'] - 1;

        $updateBookMutation = '
        mutation {
            updateBook(id: "' . $input['book_id'] . '", input: {
                title: "' . $book['title'] . '",
                author: "' . $book['author'] . '",
                category: "' . $book['category'] . '",
                stock: ' . $newStock . '
            }) {
                id
                title
                author
                category
                stock
                created_at
                updated_at
            }
        }
        ';

        $updateBookResponse = Http::withToken($token)->post('http://172.19.0.7:8082/graphql', [
            'query' => $updateBookMutation
        ]);

        if ($updateBookResponse->failed()) {
            throw new \Exception("Failed to update book stock.");
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
