<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Reservation;
use Exception;

class ReservationQuery
{
    public function getAllReservations($_, array $args)
    {
        try {
            return Reservation::all();
        } catch (Exception $e) {
            throw new Exception("Error fetching reservations: " . $e->getMessage());
        }
    }

    public function getReservationByUserId($_, array $args)
    {
        try {
            $user_id = $args['user_id'];
            return Reservation::where('user_id', $user_id)->get();
        } catch (Exception $e) {
            throw new Exception("Error fetching reservations for user: " . $e->getMessage());
        }
    }
}
