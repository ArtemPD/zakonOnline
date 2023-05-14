<?php

namespace App\Http\Controllers;

use App\Http\Services\ZakonOnlineApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BotController extends Controller
{
    public function __construct(private readonly ZakonOnlineApiService $zakonOnlineApiService)
    {
    }

    public function getResponse(Request $request): JsonResponse
    {
        $userMessage = $request->input('message');
        $maxAttempts = 2;
        $attempt = 0;
        $conclusion = '';

        do {
            try {
                $r = '';
                $prompt = 'You lawyer that speak ukrainian, which one helps generate keywords for a human (me) to easily find court decisions and precedents.' .
                    ' You must respond in JSON format {"ks": [{"k": text,"rt": int}]} k is keyword rt is success rate.' .
                    ' dont show if it lower 60 Important! Your must respond only JSON ' . $userMessage;

                // Call the OpenAI API using the callOpenAI method
                $chatCompletion = $this->callOpenAI($prompt);

                $jsonString = trim($chatCompletion['choices'][0]['message']['content']);
                $parsedJson = json_decode($jsonString, true);

                if ($parsedJson && isset($parsedJson['ks'])) {
                    foreach ($parsedJson['ks'] as $keyword) {
                        $r .= $keyword['k'] . ' ' . $keyword['rt'] . "\n";
                    }
                } else {
                    Log::error('Invalid JSON format or missing "ks" property', ['json' => $jsonString]);
                    throw new \InvalidArgumentException('Invalid JSON format or missing "ks" property');
                }

                Validator::make($parsedJson, [
                    'ks' => 'required|array',
                    'ks.*.k' => 'required|string',
                    'ks.*.rt' => 'required|integer|between:0,100',
                ])->validate();

                $document = $this->zakonOnlineApiService->getAppropriateDocument($parsedJson);

                if ($document->isEmpty()) {
                    $conclusion = 'Дайте більш розгорнуте повідомлення';
                } else {
                    // get legal position
                    $legalPosition = $this->zakonOnlineApiService->getLegalPositionId($document->first()['cause_num']);
                    // get conclusion
                    $conclusion = $this->zakonOnlineApiService->getConclusion($legalPosition->first()['id']);
                }

                return response()->json(['response' => $conclusion], 200);
            } catch (\Exception $e) {
                Log::error('Something went wrong', ['exception' => $e]);
            }

            $attempt++;
        } while ($attempt <= $maxAttempts);

        return response()->json(['error' => "Max attempts reached"], 400);

    }

    public function callOpenAI($prompt)
    {
        $apiKey = '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';
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.3,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post('https://api.openai.com/v1/chat/completions', $data);

        return $response->json();
    }
}
