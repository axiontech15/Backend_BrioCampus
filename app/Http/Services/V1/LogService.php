<?php

namespace App\Http\Services\V1;

use App\Models\Log;
use Illuminate\Database\Eloquent\Collection;

class LogService
{
    public function getAllLogs(): Collection
    {
        return Log::all();
    }

    public function getLogById(int $id): ?Log
    {
        return Log::find($id);
    }

    public function createLog(array $data): Log
    {
        return Log::create($data);
    }

    public function updateLog(int $id, array $data): bool
    {
        return Log::where('id', $id)->update($data) > 0;
    }

    public function deleteLog(int $id): bool
    {
        return Log::destroy($id) > 0;
    }
}
