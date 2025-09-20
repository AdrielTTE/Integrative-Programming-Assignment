<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'package_weight' => 'required|numeric|min:0.1|max:50',
            'package_dimensions' => 'nullable|string|max:50|regex:/^\d+x\d+x\d+$/',
            'package_contents' => 'required|string|max:500|not_regex:/[<>"\']/',
            'sender_address' => 'required|string|max:500|not_regex:/[<>"\']/',
            'recipient_address' => 'required|string|max:500|not_regex:/[<>"\']/',
            'priority' => 'nullable|in:standard,express,urgent',
            'notes' => 'nullable|string|max:1000|not_regex:/[<>"\']/',
        ];
    }

    public function messages()
    {
        return [
            'package_contents.not_regex' => 'Package contents contains invalid characters',
            'sender_address.not_regex' => 'Sender address contains invalid characters',
            'recipient_address.not_regex' => 'Recipient address contains invalid characters',
            'notes.not_regex' => 'Notes contain invalid characters',
            'package_dimensions.regex' => 'Dimensions must be in format: length x width x height (e.g., 10x20x30)',
        ];
    }

    /**
     * Get validated data with only allowed fields for mass assignment
     */
    public function getValidatedForCreation(): array
    {
        $validated = $this->validated();
        
        // Only allow specific fields for package creation
        return array_intersect_key($validated, array_flip([
            'package_weight',
            'package_dimensions', 
            'package_contents',
            'sender_address',
            'recipient_address',
            'priority',
            'notes'
        ]));
    }

    /**
     * Sanitize input data
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'package_contents' => $this->sanitizeInput($this->package_contents),
            'sender_address' => $this->sanitizeInput($this->sender_address),
            'recipient_address' => $this->sanitizeInput($this->recipient_address),
            'notes' => $this->sanitizeInput($this->notes),
        ]);
    }

    private function sanitizeInput(?string $input): ?string
    {
        if (!$input) return $input;
        
        // Remove potentially dangerous characters
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return trim($input);
    }
}