<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function search(string $searchTerm): array
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm === '') {
            return ['query' => Product::query()->where('status', 'Active'), 'did_you_mean' => null, 'original_term' => ''];
        }

        $query = Product::query()->where('status', 'Active');
        $originalQuery = clone $query;

        // 1. Exact and Partial Match (Full Phrase)
        $this->applySearchConditions($query, $searchTerm);

        if ((clone $query)->exists()) {
            return ['query' => $query, 'did_you_mean' => null, 'original_term' => $searchTerm];
        }

        // 2. Multi-word Token Match (Handles combinations like "Samsung mobile", "Nike shoe", "Apple phone", "5G phone")
        $tokens = array_filter(preg_split('/\s+/', $searchTerm));
        if (count($tokens) > 1) {
            $tokenQuery = Product::query()->where('status', 'Active');
            foreach ($tokens as $token) {
                $this->applySearchConditions($tokenQuery, $token);
            }
            if ((clone $tokenQuery)->exists()) {
                return ['query' => $tokenQuery, 'did_you_mean' => null, 'original_term' => $searchTerm];
            }
        }

        // 3. Fuzzy Search Fallback for Typos (Checks word-level distance with cached active product catalog)
        $allProductNames = \Illuminate\Support\Facades\Cache::remember('search_product_words', 600, function () {
            return Product::where('status', 'Active')->pluck('name')->toArray();
        });
        $suggestions = $this->findSimilarWords($searchTerm, $allProductNames);

        if (!empty($suggestions)) {
            $bestMatch = array_key_first($suggestions);
            $fuzzyQuery = Product::query()->where('status', 'Active');
            $this->applySearchConditions($fuzzyQuery, $bestMatch);
            if ((clone $fuzzyQuery)->exists()) {
                return ['query' => $fuzzyQuery, 'did_you_mean' => $bestMatch, 'original_term' => $searchTerm];
            }
        }

        // 4. No results
        return ['query' => $originalQuery->whereRaw('1 = 0'), 'did_you_mean' => null, 'original_term' => $searchTerm];
    }

    private function applySearchConditions(Builder $query, string $term): void
    {
        $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('tags', 'like', "%{$term}%")
              ->orWhereHas('category', function (Builder $q) use ($term) {
                  $q->where('name', 'like', "%{$term}%");
              })
              ->orWhereHas('brand', function (Builder $q) use ($term) {
                  $q->where('name', 'like', "%{$term}%");
              });
        });
    }

    private function findSimilarWords(string $searchTerm, $words, int $threshold = 2): array
    {
        $suggestions = [];
        foreach ($words as $word) {
            $wordsInName = preg_split('/\s+/', (string) $word);
            foreach ($wordsInName as $single) {
                if (strlen($single) < 3) continue;
                $distance = levenshtein(strtolower($searchTerm), strtolower($single));
                if ($distance <= $threshold) {
                    $suggestions[$single] = $distance;
                }
            }
        }
        asort($suggestions);
        return array_slice($suggestions, 0, 5, true);
    }
}
