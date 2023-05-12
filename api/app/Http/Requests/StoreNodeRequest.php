<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'ip_address' => ['required', 'ip', Rule::unique('nodes')->ignore($this->node)],
            'port' => 'required',
            'token' => 'required'
        ];
    }
}
