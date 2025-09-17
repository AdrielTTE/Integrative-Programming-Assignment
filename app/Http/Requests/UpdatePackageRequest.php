<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Assuming authorization is handled in the controller
    }

    public function rules()
    {
        return [
            'package_contents' => 'required|string|max:1000',
            'package_dimensions' => 'nullable|string|max:50|regex:/^\d+x\d+x\d+$/',
            'sender_address' => 'required|string|max:500',
            'recipient_address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            
            'package_weight' => 'prohibited',
            'priority' => 'prohibited',
            'shipping_cost' => 'prohibited',
        ];
    }

    public function messages()
    {
        return [
            'package_contents.required' => 'Package contents description is required.',
            'package_contents.max' => 'Package contents description must not exceed 1000 characters.',
            'package_dimensions.regex' => 'Dimensions must be in format: LengthxWidthxHeight (e.g., 30x20x10).',
            'sender_address.required' => 'Pickup address is required.',
            'sender_address.max' => 'Pickup address must not exceed 500 characters.',
            'recipient_address.required' => 'Delivery address is required.',
            'recipient_address.max' => 'Delivery address must not exceed 500 characters.',
            'notes.max' => 'Special instructions must not exceed 500 characters.',
            'package_weight.prohibited' => 'Package weight cannot be modified.',
            'priority.prohibited' => 'Priority cannot be modified.',
            'shipping_cost.prohibited' => 'Shipping cost cannot be modified.',
        ];
    }

    protected function prepareForValidation()
    {
        // Remove any prohibited fields from the request
        $this->request->remove('package_weight');
        $this->request->remove('priority');
        $this->request->remove('shipping_cost');
    }
}