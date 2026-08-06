<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function search(string $searchTerm): array
    {
        $query = Product::query()->where('status', 'Active');
        $originalQuery = clone $query;

        // 1. Exact and Partial Match
        $this->applySearchConditions($query, $searchTerm);
        $products = $query->get();

        if ($products->isNotEmpty()) {
            return ['query' => $query, 'did_you_mean' => null, 'original_term' => $searchTerm];
        }

        // 2. Fuzzy Search Fallback
        $allProductNames = Product::pluck('name', 'id');
        $suggestions = $this->findSimilarWords($searchTerm, $allProductNames);

        if (!empty($suggestions)) {
            $bestMatch = array_key_first($suggestions);

            // If only one good suggestion, show results for it
            if (count($suggestions) == 1) {
                $this->applySearchConditions($originalQuery, $bestMatch);
                return ['query' => $originalQuery, 'did_you_mean' => $bestMatch, 'original_term' => $searchTerm];
            }

            // If multiple suggestions, show them
            return ['query' => $originalQuery->whereRaw('1 = 0'), 'suggestions' => $suggestions, 'original_term' => $searchTerm];
        }

        // 3. No results
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

    private function findSimilarWords(string $searchTerm, $words, int $threshold = 3): array
    {
        $suggestions = [];
        foreach ($words as $word) {
            $distance = levenshtein(strtolower($searchTerm), strtolower($word));
            if ($distance <= $threshold) {
                $suggestions[$word] = $distance;
            }
        }
        asort($suggestions);
        return array_slice($suggestions, 0, 5, true);
    }
}
