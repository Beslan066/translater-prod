<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return auth()->user()->role == 1;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'locality' => 'nullable|string|max:255',
            'school' => 'nullable|string|max:255',
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:1,2,3',
        ];
    }
}
