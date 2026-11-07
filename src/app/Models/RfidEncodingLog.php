<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidEncodingLog extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'epc_id', 'encoded_by', 'reader_id',
        'encoding_data', 'status', 'error_message',
    ];

    protected $casts = [
        'encoding_data' => 'array',
    ];

    public function epc(): BelongsTo
    {
        return $this->belongsTo(Epc::class);
    }

    public function encodedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(RfidReader::class, 'reader_id');
    }
}
