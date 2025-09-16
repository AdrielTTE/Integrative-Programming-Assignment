<?php

namespace App\Http\Requests; 

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Package;

class UpdatePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // You can keep your authorization logic
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'package_weight' => 'sometimes|required|numeric|min:0.01|max:999.99',
            'package_dimensions' => 'sometimes|nullable|string|max:100|regex:/^\d+x\d+x\d+$/',
            'package_contents' => 'sometimes|required|string|max:1000',
            'sender_address' => 'sometimes|required|string|max:500',
            'recipient_address' => 'sometimes|required|string|max:500',
            'priority' => 'sometimes|required|in:' . implode(',', [
                Package::PRIORITY_STANDARD,
                Package::PRIORITY_EXPRESS,
                Package::PRIORITY_URGENT
            ]),
            'notes' => 'sometimes|nullable|string|max:1000'
        ];
    }
}