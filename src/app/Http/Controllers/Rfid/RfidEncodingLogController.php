<?php

namespace App\Http\Controllers\Rfid;

use App\Http\Controllers\Controller;
use App\Models\RfidEncodingLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfidEncodingLogController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $logs = RfidEncodingLog::query()
            ->with(['epc:id,epc', 'encodedByUser:id,name', 'reader:id,name'])
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($dateFrom, fn($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Rfid/EncodingLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
