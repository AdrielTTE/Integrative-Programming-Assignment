<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchPackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tracking_number' => 'nullable|string|max:20',
            'package_id' => 'nullable|string|max:20',
            'user_id' => 'nullable|string|exists:user,user_id',
            'package_status' => 'nullable|in:pending,processing,in_transit,out_for_delivery,delivered,cancelled,returned,failed',
            'priority' => 'nullable|in:standard,express,urgent',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_by' => 'nullable|in:created_at,updated_at,package_id,tracking_number,package_status',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:5|max:100',
        ];
    }
}