<?php

namespace App\Http\Controllers\Rfid;

use App\Enums\RfidEventType;
use App\Http\Controllers\Controller;
use App\Models\RfidReadEvent;
use App\Models\RfidReader;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfidReadEventController extends Controller
{
    public function index(Request $request): Response
    {
        $readerId = $request->input('reader_id');
        $eventType = $request->input('event_type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $processed = $request->input('processed');

        $events = RfidReadEvent::query()
            ->with(['reader:id,name', 'epc:id,epc,item_id'])
            ->when($readerId, fn($query, $readerId) => $query->where('reader_id', $readerId))
            ->when($eventType, fn($query, $eventType) => $query->where('event_type', $eventType))
            ->when($dateFrom, fn($query, $dateFrom) => $query->whereDate('first_read_at', '>=', $dateFrom))
            ->when($dateTo, fn($query, $dateTo) => $query->whereDate('first_read_at', '<=', $dateTo))
            ->when($processed !== null, fn($query) => $query->where('processed', $processed === 'true'))
            ->orderBy('first_read_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $readers = RfidReader::select('id', 'name')->get();

        return Inertia::render('Rfid/ReadEvents/Index', [
            'events' => $events,
            'readers' => $readers,
            'eventTypes' => collect(RfidEventType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'filters' => [
                'reader_id' => $readerId,
                'event_type' => $eventType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'processed' => $processed,
            ],
        ]);
    }

    public function show(RfidReadEvent $readEvent): Response
    {
        $readEvent->load(['reader', 'epc.item', 'epc.batch', 'epc.warehouse']);

        return Inertia::render('Rfid/ReadEvents/Show', [
            'event' => $readEvent,
        ]);
    }
}
