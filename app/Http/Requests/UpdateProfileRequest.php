<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => 'numeric|required|unique:users,nik',
            'name' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'email' => 'email|required|unique:users,email',
            'phone' => 'numeric|required|unique:users,phone',
            'password' => 'required|min:6',
        ];
    }
}
