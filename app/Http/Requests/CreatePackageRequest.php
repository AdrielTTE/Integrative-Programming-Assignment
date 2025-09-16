<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;

class CreatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (Auth::check()) {
            $this->merge([

                'customer_id' => Auth::user()->user_id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        //check the 'user' table for the 'user_id'.
        return [
            'customer_id' => 'required|string|max:20|exists:user,user_id',
            'package_weight' => 'required|numeric|min:0.01|max:999.99',
            'package_dimensions' => 'nullable|string|max:100|regex:/^\d+x\d+x\d+$/',
            'package_contents' => 'required|string|max:1000',
            'sender_address' => 'required|string|max:500',
            'recipient_address' => 'required|string|max:500',
            'priority' => 'required|in:' . implode(',', [
                Package::PRIORITY_STANDARD,
                Package::PRIORITY_EXPRESS,
                Package::PRIORITY_URGENT
            ]),
            'notes' => 'nullable|string|max:1000'
        ];
    }

    public function messages(): array
    {
        // You can update the message to be more accurate.
        return [
            'customer_id.exists' => 'The logged-in user is not a valid customer in the system.',
            'customer_id.exists' => 'Customer does not exist',
            'package_weight.required' => 'Package weight is required',
            'package_weight.numeric' => 'Package weight must be a number',
            'package_weight.min' => 'Package weight must be at least 0.01 kg',
            'package_dimensions.regex' => 'Package dimensions must be in format LxWxH (e.g., 10x20x30)',
            'package_contents.required' => 'Package contents description is required',
            'sender_address.required' => 'Sender address is required',
            'recipient_address.required' => 'Recipient address is required',
            'priority.required' => 'Priority is required',
            'priority.in' => 'Invalid priority selected'        ];
    }
}
