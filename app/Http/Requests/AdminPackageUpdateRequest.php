<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Package;

class AdminPackageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && str_starts_with(auth()->user()->user_id, 'AD');
    }

    public function rules()
    {
        return [
            'action' => 'sometimes|in:process,cancel,assign,deliver,return',
            'driver_id' => 'required_if:action,assign|string|regex:/^D\d{3,}$/|exists:user,user_id',
            'package_status' => 'sometimes|in:' . implode(',', array_keys(Package::getStatuses())),
            'priority' => 'sometimes|in:standard,express,urgent',
            'estimated_delivery' => 'sometimes|date|after:today',
            'sender_address' => 'sometimes|string|max:500|not_regex:/<[^>]*>/',
            'recipient_address' => 'sometimes|string|max:500|not_regex:/<[^>]*>/',
            'notes' => 'sometimes|string|max:1000|not_regex:/<[^>]*>/',
            'proof_data' => 'sometimes|array',
            'proof_data.signature' => 'sometimes|string|max:255',
            'proof_data.photo' => 'sometimes|string|max:1048576',
            'proof_data.notes' => 'sometimes|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'driver_id.regex' => 'Driver ID must be in format D001, D002, etc.',
            'driver_id.exists' => 'Selected driver does not exist.',
            'estimated_delivery.after' => 'Estimated delivery must be a future date.',
            '*.not_regex' => 'Field contains invalid HTML characters.',
            'proof_data.photo.max' => 'Photo size must not exceed 1MB.'
        ];
    }

    protected function prepareForValidation()
    {
        // Sanitize string inputs
        $fieldsToSanitize = ['sender_address', 'recipient_address', 'notes'];
        
        foreach ($fieldsToSanitize as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => strip_tags($this->input($field))
                ]);
            }
        }
    }
}