<?php

namespace App\Jobs;

use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PalmaresService;
use App\Models\Palmares;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPalmaresImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 3;

    public array $backoff = [10, 30];

    public function __construct(public int $palmaresId) {}

    public function handle(PalmaresService $palmaresService, LogService $logService): void
    {
        $palmares = Palmares::query()->findOrFail($this->palmaresId);
        $result = $palmaresService->processPendingImport($palmares);

        $logService->createLog([
            'user_id' => $palmares->uploaded_by,
            'action' => 'IMPORT_COMPLETED',
            'description' => "Palmarès {$palmares->file} traité : {$result['imported']} diplômé(s) importé(s), {$result['skipped']} ligne(s) ignorée(s).",
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $palmares = Palmares::query()->find($this->palmaresId);

        if (! $palmares) {
            return;
        }

        $palmares->update(['status' => 'rejected']);

        app(LogService::class)->createLog([
            'user_id' => $palmares->uploaded_by,
            'action' => 'IMPORT_FAILED',
            'description' => "Le traitement du palmarès {$palmares->file} a échoué. Contactez un administrateur.",
        ]);
    }
}
