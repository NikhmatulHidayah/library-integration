<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Review;

class GetAllReviewsQuery
{
    public function __invoke()
    {
        return Review::all();
    }
}
