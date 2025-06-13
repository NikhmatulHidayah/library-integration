<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Loan;
use Exception;

class LoanQuery
{
    public function getAllLoans($_, array $args)
    {
        try {
            return Loan::all();
        } catch (Exception $e) {
            throw new Exception("Error fetching loans: " . $e->getMessage());
        }
    }

    public function getLoanByUser($_, array $args)
    {
        try {
            $user_id = $args['user_id'];
            return Loan::where('user_id', $user_id)->get();
        } catch (Exception $e) {
            throw new Exception("Error fetching loans for user: " . $e->getMessage());
        }
    }
}

