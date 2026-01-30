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
     * Index.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $clients = $this->clients->list($request->user());

        return response()->json(ApiClientResource::collection($clients));
    }

    /**
     * Store.
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
