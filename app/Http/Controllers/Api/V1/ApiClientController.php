<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;

class ApiClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = ApiClient::query()
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $client = ApiClient::query()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'status' => 'active',
        ]);

        return response()->json($client, 201);
    }
}
