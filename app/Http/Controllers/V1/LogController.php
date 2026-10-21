<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\V1\LogRequest;
use Illuminate\Http\Request;
use App\Http\Services\V1\LogService;
class LogController
{
    private $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index()
    {
        return $this->logService->getAllLogs();
    }

    public function show(int $id)
    {
        return $this->logService->getLogById($id);
    }


    public function update(LogRequest $request, int $id)
    {
        return $this->logService->updateLog($id, $request->validated());
    }


    public function destroy(int $id)
    {
        return $this->logService->deleteLog($id);
    }
}
