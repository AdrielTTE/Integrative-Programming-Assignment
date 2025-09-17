<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_weight' => 'sometimes|numeric|min:0.1|max:50',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents' => 'sometimes|string|max:500',
            'sender_address' => 'sometimes|string|max:500',
            'recipient_address' => 'sometimes|string|max:500',
            'priority' => 'sometimes|in:standard,express,urgent',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}