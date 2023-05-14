<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConclusionRequest;
use App\Http\Services\ZakonOnlineApiService;
use Illuminate\Http\JsonResponse;

class ZakonOnlineApiController extends Controller
{

    public function __construct(private readonly ZakonOnlineApiService $zakonOnlineApiService)
    {
    }

    /**
     * @param ConclusionRequest $request
     * @return JsonResponse
     */
    public function GetConclusion(ConclusionRequest $request): JsonResponse
    {
        $document = $this->zakonOnlineApiService->getAppropriateDocument($request->validated());

        if ($document->isEmpty()) {
            $conclusion = '';
        } else {
            // get legal position
            $legalPosition = $this->zakonOnlineApiService->getLegalPositionId($document->first()['cause_num']);
            // get conclusion
            $conclusion = $this->zakonOnlineApiService->getConclusion($legalPosition->first()['id']);
        }

        return response()->json(['conclusion' => $conclusion]);
    }
}
