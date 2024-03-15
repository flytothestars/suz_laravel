<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'old_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ];
    }

    /**
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        $isApiRequest = $this->is('api/*'); // Check if the request is from the API

        if ($isApiRequest) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        } else {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
    }
}
