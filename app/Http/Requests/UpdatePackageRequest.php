<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Package;

class UpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && str_starts_with(auth()->user()->user_id, 'C');
    }

    public function rules()
    {
        return [
            'package_weight' => 'sometimes|numeric|min:0.01|max:999.99',
            'package_dimensions' => 'sometimes|nullable|string|max:100|regex:/^\d+x\d+x\d+$/',
            'package_contents' => 'sometimes|string|max:1000',
            'sender_address' => 'sometimes|string|max:500',
            'recipient_address' => 'sometimes|string|max:500',
            'priority' => 'sometimes|in:' . implode(',', [
                Package::PRIORITY_STANDARD,
                Package::PRIORITY_EXPRESS,
                Package::PRIORITY_URGENT
            ]),
            'notes' => 'sometimes|nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'package_weight.numeric' => 'Package weight must be a number',
            'package_weight.min' => 'Package weight must be at least 0.01 kg',
            'package_weight.max' => 'Package weight cannot exceed 999.99 kg',
            'package_dimensions.regex' => 'Package dimensions must be in format LxWxH (e.g., 10x20x30)',
            'priority.in' => 'Invalid priority selected'
        ];
    }
}