<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Keys\ApiClientResource;
use App\Services\Keys\ApiClientServiceInterface;
use Illuminate\Http\Request;

/**
 * API controller for api client endpoints.
 */
class ApiClientController extends Controller
{
    /**
     * Create a new instance.
     * @param ApiClientServiceInterface $clients
     * @return void
     */
    public function __construct(private readonly ApiClientServiceInterface $clients)
    {
    }

    /**
     * List API clients for the authenticated user.
     *
     * Returns a JSON resource collection.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $clients = $this->clients->list($request->user());

        return response()->json(ApiClientResource::collection($clients));
    }

    /**
     * Create a new API client for the authenticated user.
     *
     * Validates input and returns the created client as a JSON resource (201).
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $client = $this->clients->create($request->user(), $data['name']);

        return response()->json(ApiClientResource::make($client), 201);
    }
}
