<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Services\DeliverableFileService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverableFileController extends Controller
{
    public function show(
        string $projectUniqueId,
        string $deliverableUniqueId,
        string $fileUniqueId,
        DeliverableFileService $deliverableFileService,
    ): StreamedResponse {
        $file = $deliverableFileService->findFileForDeliverableInProject(
            $fileUniqueId,
            $deliverableUniqueId,
            $projectUniqueId,
        );

        abort_if($file === null, 404);

        Gate::authorize('download', $file);

        $inline = request()->validate([
            'disposition' => ['sometimes', 'in:inline,attachment'],
        ])['disposition'] ?? 'inline';

        $response = $deliverableFileService->streamFile(
            $file,
            request()->user(),
            inline: $inline !== 'attachment',
        );

        abort_if($response === null, 404);

        return $response;
    }
}
