<?php

namespace App\Http\Services;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ZakonOnlineApiService
{


    /**
     * @param array $keyword
     * @return Collection
     */
    public function getAppropriateDocument(array $keyword): Collection
    {
        // Prepare array for keywords with success_rate > 80
        $keywords_high_success_rate = [];

        foreach ($keyword['ks'] as $keyword) {
            if ($keyword['rt'] >= 80) {
                $keywords_high_success_rate[] = $keyword['k'];
            }
        }

        // Create search string with OR operator
        $searchString = implode(' AND ', $keywords_high_success_rate);

        // Prepare parameters
        $params = [
            'target' => 'text',
            'results' => 'lite',
            'page' => '1',
            'limit' => '10',
            'with_linked_docs' => 1,
            'where[is_have_legal_position]' => '1',
            'order[references_count]' => '',
            'search' => $searchString, // Add the search string to the parameters
        ];

        // Make the request
        $response = Http::withHeaders([
            'X-App-Token' => config('zakon.token'),
            'Accept' => 'application/json'
        ])->get('https://court.searcher.api.zakononline.com.ua/v1/search', $params);

        // Check if request was successful
        if ($response->successful()) {
            $responseData = json_decode($response->body(), true);
            return $responseData ? collect($responseData) : collect([]);
        } else {
            // Return empty array
            return collect([]);
        }

    }

    /**
     * @param string $documentCauseNum
     * @return Collection
     */
    public function getLegalPositionId(string $documentCauseNum): Collection
    {
        // Prepare parameters
        $params = [
            'page' => '1',
            'limit' => '1',
            'search' => $documentCauseNum,
        ];

        // Make the request
        $response = Http::withHeaders([
            'X-App-Token' => config('zakon.token'),
            'Accept' => 'application/json'
        ])->get('https://courtpractice.searcher.api.zakononline.com.ua/v1/documents', $params);

        // Check if request was successful
        if ($response->successful()) {
            $responseData = json_decode($response->body(), true);

            return $responseData ? collect($responseData) : collect([]);
        } else {
            // Return empty array
            return collect([]);
        }
    }

    /**
     * @param int $legalPositionId
     * @return string
     */
    public function getConclusion(int $legalPositionId): string
    {
        // Make the request
        $response = Http::withHeaders([
            'X-App-Token' => config('zakon.token'),
            'Accept' => 'application/json'
        ])->get("https://courtpractice.searcher.api.zakononline.com.ua/v1/document/by/id/{$legalPositionId}");

        // Check if request was successful
        if ($response->successful()) {
            $responseData = json_decode($response->body(), true);

            /** @var string $html */
            $html = collect($responseData)->first()['text'];

            return $this->extractTextAfterConclusion($html);
        } else {
            // Return empty array
            return '';
        }
    }

    /**
     * @param string $html
     * @return string
     */
    public function extractTextAfterConclusion(string $html): string
    {
        $crawler = new Crawler($html);

        $conclusionText = '';

        $crawler->filter('p')->each(function (Crawler $node) use (&$conclusionText) {
            if (str_contains($node->html(), '<strong>Висновки:</strong>') || str_contains($node->html(), '<strong>ВИСНОВКИ:</strong>')) {
                $conclusionText = trim($node->text());
                return false; // This will break the loop
            }
        });

        return $conclusionText;
    }

}
