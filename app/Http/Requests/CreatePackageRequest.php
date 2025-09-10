<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Package;

class CreatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add authorization logic here
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|string|max:20|exists:customer,customer_id',
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

    public function messages()
    {
        return [
            'customer_id.required' => 'Customer ID is required',
            'customer_id.exists' => 'Customer does not exist',
            'package_weight.required' => 'Package weight is required',
            'package_weight.numeric' => 'Package weight must be a number',
            'package_weight.min' => 'Package weight must be at least 0.01 kg',
            'package_dimensions.regex' => 'Package dimensions must be in format LxWxH (e.g., 10x20x30)',
            'package_contents.required' => 'Package contents description is required',
            'sender_address.required' => 'Sender address is required',
            'recipient_address.required' => 'Recipient address is required',
            'priority.required' => 'Priority is required',
            'priority.in' => 'Invalid priority selected'
        ];
    }
}

class UpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_weight' => 'sometimes|numeric|min:0.01|max:999.99',
            'package_dimensions' => 'sometimes|nullable|string|max:100|regex:/^\d+x\d+x\d+$/',
            'package_contents' => 'sometimes|string|max:1000',
            'sender_address' => 'sometimes|string|max:500',
            'recipient_address' => 'sometimes|string|max:500',
            'package_status' => 'sometimes|in:' . implode(',', [
                Package::STATUS_PENDING,
                Package::STATUS_PROCESSING,
                Package::STATUS_IN_TRANSIT,
                Package::STATUS_OUT_FOR_DELIVERY,
                Package::STATUS_DELIVERED,
                Package::STATUS_CANCELLED,
                Package::STATUS_RETURNED,
                Package::STATUS_FAILED
            ]),
            'priority' => 'sometimes|in:' . implode(',', [
                Package::PRIORITY_STANDARD,
                Package::PRIORITY_EXPRESS,
                Package::PRIORITY_URGENT
            ]),
            'notes' => 'sometimes|nullable|string|max:1000'
        ];
    }
}

class BulkUpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'required|string|exists:package,package_id',
            'action' => 'required|in:update_status,assign_driver',
            'value' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'package_ids.required' => 'At least one package must be selected',
            'package_ids.array' => 'Package IDs must be an array',
            'package_ids.*.exists' => 'One or more packages do not exist',
            'action.required' => 'Action is required',
            'action.in' => 'Invalid action selected',
            'value.required' => 'Value is required for the selected action'
        ];
    }
}

class SearchPackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tracking_number' => 'sometimes|nullable|string|max:50',
            'package_id' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'package_status' => 'sometimes|nullable|array',
            'package_status.*' => 'string|in:' . implode(',', [
                Package::STATUS_PENDING,
                Package::STATUS_PROCESSING,
                Package::STATUS_IN_TRANSIT,
                Package::STATUS_OUT_FOR_DELIVERY,
                Package::STATUS_DELIVERED,
                Package::STATUS_CANCELLED,
                Package::STATUS_RETURNED,
                Package::STATUS_FAILED
            ]),
            'priority' => 'sometimes|nullable|in:' . implode(',', [
                Package::PRIORITY_STANDARD,
                Package::PRIORITY_EXPRESS,
                Package::PRIORITY_URGENT
            ]),
            'customer_id' => 'sometimes|nullable|string|exists:customer,customer_id',
            'date_from' => 'sometimes|nullable|date',
            'date_to' => 'sometimes|nullable|date|after_or_equal:date_from',
            'weight_min' => 'sometimes|nullable|numeric|min:0',
            'weight_max' => 'sometimes|nullable|numeric|min:0|gte:weight_min',
            'sort_by' => 'sometimes|nullable|in:created_at,package_weight,shipping_cost,estimated_delivery',
            'sort_order' => 'sometimes|nullable|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'paginate' => 'sometimes|boolean'
        ];
    }
}