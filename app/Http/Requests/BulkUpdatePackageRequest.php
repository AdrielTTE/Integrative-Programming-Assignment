<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'action' => 'required|in:process,cancel,assign,update_status',
            'value' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'package_ids.required' => 'At least one package must be selected',
            'package_ids.*.exists' => 'One or more selected packages do not exist',
            'action.required' => 'Action is required',
            'action.in' => 'Invalid action selected',
        ];
    }
}