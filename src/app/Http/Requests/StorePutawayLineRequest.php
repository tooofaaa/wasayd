<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class StorePutawayLineRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('operations.update');
    }

    public function rules(): array
    {
        return [
            'to_bin_id' => ['required', 'integer', 'exists:bins,id'],
        ];
    }
}
