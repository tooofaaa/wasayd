<?php

namespace App\Services\Rfid;

use App\Enums\RfidEventType;
use App\Models\Epc;
use App\Models\RfidReadEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfidReadService
{
    /**
     * Process a read event from an RFID reader
     */
    public function processReadEvent(array $eventData): RfidReadEvent
    {
        $companyId = auth()->user()->company_id;

        DB::beginTransaction();
        try {
            // Find or create EPC record
            $epc = Epc::where('company_id', $companyId)
                ->where('epc', $eventData['epc_code'])
                ->first();

            // Create read event
            $readEvent = RfidReadEvent::create([
                'company_id' => $companyId,
                'reader_id' => $eventData['reader_id'],
                'epc_id' => $epc?->id,
                'epc_code' => $eventData['epc_code'],
                'rssi' => $eventData['rssi'] ?? null,
                'antenna_port' => $eventData['antenna_port'] ?? null,
                'read_count' => $eventData['read_count'] ?? 1,
                'first_read_at' => $eventData['timestamp'] ?? now(),
                'last_read_at' => $eventData['timestamp'] ?? now(),
                'event_type' => $eventData['event_type'] ?? RfidEventType::Read,
                'session_id' => $eventData['session_id'] ?? null,
                'processed' => false,
            ]);

            // Update EPC last seen info
            if ($epc) {
                $epc->update([
                    'last_seen_at' => $readEvent->last_read_at,
                    'last_seen_reader' => $eventData['reader_id'],
                ]);
            }

            DB::commit();
            return $readEvent;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deduplicate reads within a time window
     * Multiple reads of same EPC within window = 1 unit
     */
    public function deduplicateReads(Collection $readEvents, int $windowSeconds = 5): Collection
    {
        $deduplicated = collect();
        $groupedByEpc = $readEvents->groupBy('epc_code');

        foreach ($groupedByEpc as $epcCode => $events) {
            $sortedEvents = $events->sortBy('first_read_at');
            $currentGroup = null;
            $groupStartTime = null;

            foreach ($sortedEvents as $event) {
                if ($currentGroup === null) {
                    // Start new group
                    $currentGroup = $event;
                    $groupStartTime = $event->first_read_at;
                } else {
                    $timeDiff = $event->first_read_at->diffInSeconds($groupStartTime);

                    if ($timeDiff <= $windowSeconds) {
                        // Within window, merge into current group
                        $currentGroup->read_count += $event->read_count;
                        $currentGroup->last_read_at = $event->last_read_at;
                    } else {
                        // Outside window, save current group and start new one
                        $deduplicated->push($currentGroup);
                        $currentGroup = $event;
                        $groupStartTime = $event->first_read_at;
                    }
                }
            }

            // Add last group
            if ($currentGroup) {
                $deduplicated->push($currentGroup);
            }
        }

        return $deduplicated;
    }

    /**
     * Get all read events for a session
     */
    public function getSessionReads(string $sessionId): Collection
    {
        return RfidReadEvent::where('session_id', $sessionId)
            ->with(['epc', 'reader'])
            ->orderBy('first_read_at')
            ->get();
    }

    /**
     * Mark read events as processed
     */
    public function markAsProcessed(Collection $readEvents): void
    {
        $ids = $readEvents->pluck('id')->toArray();
        RfidReadEvent::whereIn('id', $ids)->update(['processed' => true]);
    }

    /**
     * Get unprocessed read events for a reader
     */
    public function getUnprocessedReads(int $readerId, ?int $limit = null): Collection
    {
        $query = RfidReadEvent::where('reader_id', $readerId)
            ->unprocessed()
            ->with(['epc', 'reader'])
            ->orderBy('first_read_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Aggregate read statistics for a reader
     */
    public function getReaderStatistics(int $readerId, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $query = RfidReadEvent::where('reader_id', $readerId);

        if ($from) {
            $query->where('first_read_at', '>=', $from);
        }

        if ($to) {
            $query->where('first_read_at', '<=', $to);
        }

        $totalReads = $query->sum('read_count');
        $uniqueEpcs = $query->distinct('epc_code')->count('epc_code');
        $eventsByType = $query->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->get();

        return [
            'total_reads' => $totalReads,
            'unique_epcs' => $uniqueEpcs,
            'events_by_type' => $eventsByType,
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
        ];
    }
}
