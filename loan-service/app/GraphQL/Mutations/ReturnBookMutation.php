<?php

namespace App\GraphQL\Mutations;

use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ReturnBookMutation
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

        $loan = Loan::findOrFail($args['id']);
        $bookId = $loan->book_id;

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
            'query' => sprintf($graphqlQuery, $bookId)
        ]);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch book details.");
        }

        $book = $response->json('data.getBookById');

        if (!$book) {
            throw new \Exception("Book not found.");
        }

        $newStock = $book['stock'] + 1;

        $input = [
            'title' => $book['title'],
            'author' => $book['author'],
            'category' => $book['category'],
            'stock' => $newStock,
        ];

        $updateBookResponse = Http::withToken($token)->post('http://172.19.0.7:8082/graphql', [
            'query' => '
                mutation {
                  updateBook(id: "' . $bookId . '", input: {
                    title: "' . $input['title'] . '",
                    author: "' . $input['author'] . '",
                    category: "' . $input['category'] . '",
                    stock: ' . $input['stock'] . '
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
            '
        ]);

        if ($updateBookResponse->failed()) {
            throw new \Exception("Failed to update book.");
        }

        $loan->status = 'returned';
        $loan->save();

        return $loan;
    }
}
