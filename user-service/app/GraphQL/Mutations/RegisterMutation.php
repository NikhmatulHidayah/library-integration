<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterMutation
{
    public function __invoke($_, array $args)
    {
        $validator = Validator::make($args, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $user = User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => $args['password'],
            'role' => $args['role'],
        ]);

        return $user;
    }
}
