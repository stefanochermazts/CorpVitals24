<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DTO\FetchKpiMetricsQuery;
use App\Services\KpiService;

class KpisController extends Controller
{
    public function __construct(private readonly KpiService $service)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'periodId' => 'required|integer',
            'companyId' => 'required|integer',
            'kpiCodes' => 'array',
            'kpiCodes.*' => 'string',
        ]);
        $codes = $request->input('kpiCodes', []);
        $query = new FetchKpiMetricsQuery((int) $request->integer('companyId'), (int) $request->integer('periodId'), $codes);
        return response()->json(['data' => $this->service->getSnapshot($query)]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'periodId' => 'required|integer',
            'kpiCodes' => 'required|array|min:1',
            'kpiCodes.*' => 'string',
        ]);
        $jobId = (string) str()->uuid();
        return response()->json(['jobId' => $jobId], 202);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);
        return response()->json(['accepted' => true], 202);
    }
}


