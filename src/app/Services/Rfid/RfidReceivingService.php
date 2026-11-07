<?php

namespace App\Services\Rfid;

use App\Models\Receiving;
use App\Models\RfidReadEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfidReceivingService
{
    public function __construct(
        private RfidReadService $rfidReadService
    ) {}

    /**
     * Process RFID read events for a receiving order
     * Matches EPCs to receiving lines and updates quantities
     */
    public function processRfidReceiving(int $receivingId, Collection $readEvents): array
    {
        $receiving = Receiving::with(['lines.item', 'lines.batch'])
            ->findOrFail($receivingId);

        if ($receiving->status !== 'pending') {
            throw new \InvalidArgumentException('Receiving order must be in pending status');
        }

        DB::beginTransaction();
        try {
            // Deduplicate reads
            $deduplicatedReads = $this->rfidReadService->deduplicateReads($readEvents);

            $matched = [];
            $unmatched = [];

            foreach ($deduplicatedReads as $readEvent) {
                $epc = $readEvent->epc;

                if (!$epc) {
                    $unmatched[] = [
                        'epc_code' => $readEvent->epc_code,
                        'reason' => 'EPC not found in system',
                    ];
                    continue;
                }

                // Find matching receiving line
                $matchingLine = $receiving->lines
                    ->where('item_id', $epc->item_id)
                    ->where('batch_id', $epc->batch_id)
                    ->first();

                if (!$matchingLine) {
                    $unmatched[] = [
                        'epc_code' => $readEvent->epc_code,
                        'item' => $epc->item->name ?? 'Unknown',
                        'batch' => $epc->batch->batch_number ?? 'Unknown',
                        'reason' => 'No matching receiving line',
                    ];
                    continue;
                }

                // Check if already received
                if ($matchingLine->received_quantity >= $matchingLine->expected_quantity) {
                    $unmatched[] = [
                        'epc_code' => $readEvent->epc_code,
                        'item' => $epc->item->name ?? 'Unknown',
                        'reason' => 'Line already fully received',
                    ];
                    continue;
                }

                // Match successful
                $matched[] = [
                    'epc_id' => $epc->id,
                    'epc_code' => $readEvent->epc_code,
                    'item' => $epc->item->name ?? 'Unknown',
                    'batch' => $epc->batch->batch_number ?? 'Unknown',
                    'receiving_line_id' => $matchingLine->id,
                    'read_event_id' => $readEvent->id,
                ];

                // Update EPC warehouse location
                $epc->update([
                    'warehouse_id' => $receiving->warehouse_id,
                    'current_location_id' => $receiving->warehouse_id,
                ]);
            }

            // Mark read events as processed
            $this->rfidReadService->markAsProcessed($deduplicatedReads);

            DB::commit();

            return [
                'matched' => $matched,
                'unmatched' => $unmatched,
                'summary' => [
                    'total_reads' => $readEvents->count(),
                    'deduplicated_reads' => $deduplicatedReads->count(),
                    'matched_count' => count($matched),
                    'unmatched_count' => count($unmatched),
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Auto-complete receiving lines based on RFID reads
     */
    public function autoCompleteReceivingLines(int $receivingId, array $matchedEpcs): void
    {
        $receiving = Receiving::with('lines')->findOrFail($receivingId);

        DB::beginTransaction();
        try {
            // Group matched EPCs by receiving line
            $epcsByLine = collect($matchedEpcs)->groupBy('receiving_line_id');

            foreach ($epcsByLine as $lineId => $epcs) {
                $line = $receiving->lines->firstWhere('id', $lineId);

                if ($line) {
                    $newReceivedQty = min(
                        $line->received_quantity + $epcs->count(),
                        $line->expected_quantity
                    );

                    $line->update([
                        'received_quantity' => $newReceivedQty,
                        'received_at' => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
