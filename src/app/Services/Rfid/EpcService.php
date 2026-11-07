<?php

namespace App\Services\Rfid;

use App\Enums\EpcStatus;
use App\Models\Epc;
use App\Models\RfidEncodingLog;
use App\Models\RfidReadEvent;
use Illuminate\Support\Facades\DB;

class EpcService
{
    /**
     * Commission an EPC (activate it for use)
     */
    public function commissionEpc(Epc $epc, ?int $readerId = null): Epc
    {
        if (!$epc->canTransitionTo(EpcStatus::Active)) {
            throw new \InvalidArgumentException(
                "Cannot commission EPC in {$epc->status->value} status"
            );
        }

        DB::beginTransaction();
        try {
            $epc->update([
                'status' => EpcStatus::Active,
                'encoded_at' => now(),
                'encoded_by' => auth()->id(),
            ]);

            // Log the encoding
            RfidEncodingLog::create([
                'company_id' => $epc->company_id,
                'epc_id' => $epc->id,
                'encoded_by' => auth()->id(),
                'reader_id' => $readerId,
                'encoding_data' => [
                    'previous_status' => EpcStatus::Unassigned->value,
                    'new_status' => EpcStatus::Active->value,
                    'action' => 'commission',
                ],
                'status' => 'success',
            ]);

            DB::commit();
            return $epc->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Decommission an EPC (mark as no longer usable)
     */
    public function decommissionEpc(Epc $epc, string $reason): Epc
    {
        if (!$epc->canTransitionTo(EpcStatus::Decommissioned)) {
            throw new \InvalidArgumentException(
                "Cannot decommission EPC in {$epc->status->value} status"
            );
        }

        DB::beginTransaction();
        try {
            $previousStatus = $epc->status;

            $epc->update([
                'status' => EpcStatus::Decommissioned,
                'metadata' => array_merge($epc->metadata ?? [], [
                    'decommissioned_at' => now()->toISOString(),
                    'decommissioned_by' => auth()->id(),
                    'decommission_reason' => $reason,
                ]),
            ]);

            // Log the decommissioning
            RfidEncodingLog::create([
                'company_id' => $epc->company_id,
                'epc_id' => $epc->id,
                'encoded_by' => auth()->id(),
                'encoding_data' => [
                    'previous_status' => $previousStatus->value,
                    'new_status' => EpcStatus::Decommissioned->value,
                    'action' => 'decommission',
                    'reason' => $reason,
                ],
                'status' => 'success',
            ]);

            DB::commit();
            return $epc->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate a batch of EPCs for an item
     * SGTIN-96 format: Header (8) + Filter (3) + Partition (3) + Company Prefix + Item Ref + Serial
     */
    public function generateBatch(int $itemId, int $quantity, string $tagType = 'SGTIN-96'): array
    {
        $companyId = auth()->user()->company_id;
        $companyPrefix = $this->getCompanyPrefix($companyId);

        DB::beginTransaction();
        try {
            $epcs = [];
            $startSerial = $this->getNextSerialNumber($companyId, $itemId);

            for ($i = 0; $i < $quantity; $i++) {
                $serial = $startSerial + $i;
                $sgtin = $this->generateSgtin96($companyPrefix, $itemId, $serial);
                $epcHex = $this->sgtinToEpcHex($sgtin);

                $epc = Epc::create([
                    'company_id' => $companyId,
                    'item_id' => $itemId,
                    'epc' => $epcHex,
                    'sgtin' => $sgtin,
                    'status' => EpcStatus::Unassigned,
                    'metadata' => [
                        'tag_type' => $tagType,
                        'serial_number' => $serial,
                        'generated_at' => now()->toISOString(),
                        'generated_by' => auth()->id(),
                    ],
                ]);

                $epcs[] = $epc;
            }

            DB::commit();
            return $epcs;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get EPC read history
     */
    public function getEpcHistory(Epc $epc): array
    {
        $readEvents = RfidReadEvent::where('epc_id', $epc->id)
            ->with('reader:id,name')
            ->orderBy('first_read_at', 'desc')
            ->get();

        $encodingLogs = RfidEncodingLog::where('epc_id', $epc->id)
            ->with('encodedByUser:id,name', 'reader:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'read_events' => $readEvents,
            'encoding_logs' => $encodingLogs,
            'lifecycle' => $this->buildLifecycleTimeline($epc, $readEvents, $encodingLogs),
        ];
    }

    /**
     * Get company prefix (7 digits for SGTIN-96 with partition value 3)
     */
    private function getCompanyPrefix(int $companyId): string
    {
        // In production, this should be stored in company settings
        // For now, generate a deterministic prefix based on company ID
        return str_pad((string) ($companyId % 10000000), 7, '0', STR_PAD_LEFT);
    }

    /**
     * Get next serial number for item
     */
    private function getNextSerialNumber(int $companyId, int $itemId): int
    {
        $lastEpc = Epc::where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->whereNotNull('metadata->serial_number')
            ->orderByRaw("CAST(JSON_EXTRACT(metadata, '$.serial_number') AS UNSIGNED) DESC")
            ->first();

        return $lastEpc ? ((int) data_get($lastEpc->metadata, 'serial_number', 0)) + 1 : 1;
    }

    /**
     * Generate SGTIN-96 URI (e.g., urn:epc:id:sgtin:0614141.812345.10000)
     */
    private function generateSgtin96(string $companyPrefix, int $itemId, int $serial): string
    {
        $itemRef = str_pad((string) $itemId, 6, '0', STR_PAD_LEFT);
        return "urn:epc:id:sgtin:{$companyPrefix}.{$itemRef}.{$serial}";
    }

    /**
     * Convert SGTIN to EPC hex representation (simplified)
     */
    private function sgtinToEpcHex(string $sgtin): string
    {
        // Extract components from URI format
        preg_match('/sgtin:(\d+)\.(\d+)\.(\d+)/', $sgtin, $matches);
        $companyPrefix = $matches[1];
        $itemRef = $matches[2];
        $serial = $matches[3];

        // SGTIN-96 binary format (simplified):
        // Header (8 bits) = 0x30 for SGTIN-96
        // Filter (3 bits) = 0 (all others)
        // Partition (3 bits) = 3 (7-6 split for company-item)
        // Company Prefix (24 bits for partition 3)
        // Item Reference (20 bits for partition 3)
        // Serial (38 bits)

        $header = '30'; // SGTIN-96 header
        $filterPartition = '18'; // Filter 0 + Partition 3 in hex

        // Pack components (simplified representation)
        $hex = $header . $filterPartition .
               str_pad(dechex((int)$companyPrefix), 7, '0', STR_PAD_LEFT) .
               str_pad(dechex((int)$itemRef), 6, '0', STR_PAD_LEFT) .
               str_pad(dechex((int)$serial), 10, '0', STR_PAD_LEFT);

        return strtoupper(substr($hex, 0, 24)); // 96 bits = 24 hex chars
    }

    /**
     * Build lifecycle timeline from events and logs
     */
    private function buildLifecycleTimeline(Epc $epc, $readEvents, $encodingLogs): array
    {
        $timeline = [];

        // Add creation event
        $timeline[] = [
            'type' => 'created',
            'timestamp' => $epc->created_at,
            'description' => 'EPC generated',
        ];

        // Add encoding events
        foreach ($encodingLogs as $log) {
            $timeline[] = [
                'type' => 'encoding',
                'timestamp' => $log->created_at,
                'description' => data_get($log->encoding_data, 'action', 'encoded'),
                'user' => $log->encodedByUser?->name,
                'reader' => $log->reader?->name,
            ];
        }

        // Add first read event
        if ($readEvents->isNotEmpty()) {
            $firstRead = $readEvents->last();
            $timeline[] = [
                'type' => 'first_read',
                'timestamp' => $firstRead->first_read_at,
                'description' => 'First RFID read',
                'reader' => $firstRead->reader?->name,
            ];
        }

        // Sort by timestamp
        usort($timeline, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        return $timeline;
    }
}
