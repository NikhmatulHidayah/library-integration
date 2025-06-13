<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Exception;

class UserQuery
{
    /**
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function users($_, array $args)
    {
        try {
            return User::all();
        } catch (Exception $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    /**
     *
     * @param $_
     * @param array $args
     * @return \App\Models\User
     */
    public function user($_, array $args)
    {
        try {
            return User::findOrFail($args['id']);
        } catch (Exception $e) {
            throw new Exception("Error fetching user with ID {$args['id']}: " . $e->getMessage());
        }
    }
}
