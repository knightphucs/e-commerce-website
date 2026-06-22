<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isEditingSelf()) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user')->id)],
            ];
        }

        return [
            'role' => ['required', 'in:admin,editor,customer'],
            'status' => ['required', 'in:active,blocked'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * Determine whether the admin is editing their own account.
     */
    public function isEditingSelf(): bool
    {
        return $this->route('user')->is($this->user());
    }
}
