<?php

namespace App\GraphQL\Mutations;

use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UpdateReservationStatusMutation
{
    public function __invoke($_, array $args)
    {
        try {
            JWTAuth::parseToken()->getPayload();
        } catch (\Exception $e) {
            throw new \Exception("Unauthorized: " . $e->getMessage());
        }

        $id = $args['id'];
        $status = $args['status'];

        $validator = Validator::make(['id' => $id, 'status' => $status], [
            'id' => 'required|integer|exists:reservations,id',
            'status' => 'required|in:pending,approved,cancelled',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $reservation = Reservation::findOrFail($id);
        $reservation->status = $status;
        $reservation->save();

        return $reservation;
    }
}
