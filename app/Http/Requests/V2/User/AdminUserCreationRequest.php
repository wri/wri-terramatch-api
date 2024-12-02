<?php

namespace App\Http\Requests\V2\User;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserCreationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_address' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email_address',
            ],
            'role' => 'required|string',
            'job_role' => 'sometimes|nullable|string|max:255',
            'country' => 'sometimes|nullable|string|max:2',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'program' => 'sometimes|nullable|string|max:255',
            'organisation' => [
                'sometimes',
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (! empty($value) && empty($value['uuid'])) {
                        $fail('The organisation must contain a uuid.');
                    }
                },
            ],
            'monitoring_organisations' => 'sometimes|array',
            'monitoring_organisations.*' => 'uuid|exists:organisations,uuid',
            'direct_frameworks' => 'sometimes|array',
            'direct_frameworks.*' => 'string|exists:frameworks,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'email_address.unique' => 'This email address is already in use.',
            'role.in' => 'Invalid role selected.',
            'organisation.uuid' => 'Invalid organisation identifier.',
            'organisation.exists' => 'Organisation not found.',
            'country.max' => 'Country code must be 2 characters long.',
            'phone_number.max' => 'Phone number cannot exceed 20 characters.',
        ];
    }
}
