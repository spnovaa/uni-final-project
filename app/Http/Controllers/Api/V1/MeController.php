<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Services\User\UserServiceInterface;
use Illuminate\Http\Request;

/**
 * API controller for me endpoints.
 */
class MeController extends Controller
{
    /**
     * Create a new instance.
     * @param UserServiceInterface $users
     * @return void
     */
    public function __construct(private readonly UserServiceInterface $users)
    {
    }

    /**
     * Get the authenticated user profile.
     *
     * Delegates to the user service, which may serve this request from cache.
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        $user = $this->users->getProfile($request->user());

        return response()->json(UserResource::make($user));
    }

    /**
     * Update the authenticated user profile.
     *
     * Validates input, persists profile changes (including optional profile image upload),
     * and invalidates any cached profile data.
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'profile_image' => ['sometimes', 'file', 'image', 'max:2048'],
        ]);

        $profileImage = $request->file('profile_image');

        $user = $this->users->updateProfile($request->user(), $data, $profileImage);

        return response()->json(UserResource::make($user));
    }
}
