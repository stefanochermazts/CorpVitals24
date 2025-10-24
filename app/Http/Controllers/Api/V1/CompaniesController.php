<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));
        $companies = Company::query()
            ->select(['id','tenant_id','name','sector','base_currency','fiscal_year_start'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $companies->items(),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
                'last_page' => $companies->lastPage(),
            ],
            'links' => [
                'first' => $companies->url(1),
                'last' => $companies->url($companies->lastPage()),
                'prev' => $companies->previousPageUrl(),
                'next' => $companies->nextPageUrl(),
            ],
        ]);
    }
}


