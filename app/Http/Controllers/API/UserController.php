<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\User\UserCollection;
use App\Http\Resources\API\User\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function profile(): UserResource
    {
        $user = auth()->user();

        return UserResource::make($user);
    }

    public function list(): UserCollection
    {
        return UserCollection::make(User::paginate(15));
    }
}
