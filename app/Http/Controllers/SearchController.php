<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Enums\ProductSearchCriteria;
use App\Exceptions\SearchCriteriaNotImplementedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    public function show(Request $request, SearchService $service)
    {
        $validated = $request->validate([
            'customer_group' => 'required|string',
            'criteria' => [Rule::enum(ProductSearchCriteria::class)],
            //'value' => 'required|int',
            'value' => 'required|string',
            'page_size' => 'int|min:1|max:20'
        ]);

        $page_size = $validated['page_size'] ?? 10;

        Log::info("Consulta de productos por criterio: {$validated['criteria']} y valor: {$validated['value']}");

        try {
            return $service
                ->searchBy($validated['customer_group'], $validated['criteria'], $validated['value'], $page_size)
                ->withQueryString();
        } catch (SearchCriteriaNotImplementedException $e) {
            return response()->json([
                'message' => $e->message(),
            ], 400);
        }
    }
}
