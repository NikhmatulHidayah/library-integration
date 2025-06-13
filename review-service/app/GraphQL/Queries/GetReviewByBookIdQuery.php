<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Review;

class GetReviewByBookIdQuery
{
    public function __invoke($root, array $args)
    {
        return Review::where('book_id', $args['book_id'])->get();
    }
}
