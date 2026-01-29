<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Services\User\UserServiceInterface;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(private readonly UserServiceInterface $users)
    {
    }

    public function show(Request $request)
    {
        return response()->json(UserResource::make($request->user()));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
        ]);

        $user = $this->users->updateProfile($request->user(), $data);

        return response()->json(UserResource::make($user));
    }
}
