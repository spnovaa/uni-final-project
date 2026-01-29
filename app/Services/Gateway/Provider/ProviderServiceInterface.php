<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use Illuminate\Support\Collection;

interface ProviderServiceInterface
{
    public function list(): Collection;

    public function create(array $data): Provider;

    public function findOrFail(int $id): Provider;
}
